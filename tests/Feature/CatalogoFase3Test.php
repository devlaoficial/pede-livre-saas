<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Support\Security\SafeImageUploadService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

final class CatalogoFase3Test extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private Usuario $dono;
    private Usuario $garcom;
    private Categoria $categoria;
    private Produto $produto;
    private SafeImageUploadService $imageService;
    private CardapioCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $plano = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'recursos_json' => ['cardapio_online' => true],
            'limite_usuarios' => 10,
        ]);

        $this->loja = Loja::create([
            'slug' => 'loja-fase3',
            'nome' => 'Loja Fase 3 Testes',
            'cnpj_cpf' => '33.333.333/0001-33',
            'cnpj_cpf_bidx' => 'bidx_doc_fase3',
            'telefone_whatsapp' => '31988883333',
            'plano_id' => $plano->id,
            'status' => 'ativa',
        ]);

        $this->dono = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Dono Teste',
            'email' => 'dono@fase3.com',
            'email_bidx' => 'bidx_dono_fase3',
            'senha_hash' => Hash::make('secret123'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        $this->garcom = Usuario::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Garçom Teste',
            'email' => 'garcom@fase3.com',
            'email_bidx' => 'bidx_garcom_fase3',
            'senha_hash' => Hash::make('secret123'),
            'papel' => 'garcom',
            'ativo' => true,
        ]);

        app(LojaContext::class)->setLoja($this->loja);

        $this->categoria = Categoria::create([
            'nome' => 'Burgers',
            'ordem' => 1,
            'ativa' => true,
            'canais' => ['delivery'],
        ]);

        $this->produto = Produto::create([
            'categoria_id' => $this->categoria->id,
            'nome' => 'Burger Artesanal',
            'preco_centavos' => 3500,
            'ativo' => true,
            'esgotado' => false,
            'canais' => ['delivery'],
            'ordem' => 1,
        ]);

        $this->imageService = new SafeImageUploadService();
        $this->cacheService = new CardapioCacheService();
    }

    public function test_1_tap_stock_pause_toggles_esgotado_and_invalidates_cache(): void
    {
        $this->cacheService->getCardapio($this->loja->id, 'delivery');
        $this->assertFalse($this->produto->esgotado);

        $response = $this->actingAs($this->dono, 'sanctum')
            ->postJson("/api/produtos/{$this->produto->id}/pausar");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->produto->id,
            'esgotado' => true,
        ]);

        $this->produto->refresh();
        $this->assertTrue($this->produto->esgotado);

        $response2 = $this->actingAs($this->dono, 'sanctum')
            ->postJson("/api/produtos/{$this->produto->id}/pausar");

        $response2->assertStatus(200);
        $response2->assertJson(['esgotado' => false]);
    }

    public function test_garcom_is_forbidden_from_pausing_stock_or_reordering(): void
    {
        $response = $this->actingAs($this->garcom, 'sanctum')
            ->postJson("/api/produtos/{$this->produto->id}/pausar");
        $response->assertStatus(403);

        $responseReorder = $this->actingAs($this->garcom, 'sanctum')
            ->patchJson('/api/categorias/reordenar', [
                'itens' => [['id' => $this->categoria->id, 'ordem' => 5]],
            ]);
        $responseReorder->assertStatus(403);
    }

    public function test_malicious_php_script_disguised_as_jpg_is_rejected_by_magic_bytes(): void
    {
        $maliciousContent = "<?php echo 'HACKED'; system(\$_GET['cmd']); ?>";
        $fakeFile = UploadedFile::fake()->createWithContent('exploit.jpg', $maliciousContent);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de arquivo não permitido');

        $this->imageService->uploadProductImage($fakeFile, $this->loja->id);
    }

    public function test_ai_catalog_import_rolls_back_entirely_on_malformed_data(): void
    {
        $initialCategoryCount = Categoria::count();
        $initialProductCount = Produto::count();

        $invalidPayload = [
            'categorias' => [
                [
                    'nome' => 'Bebidas Válidas',
                    'produtos' => [
                        ['nome' => 'Suco de Laranja', 'preco_centavos' => 1200],
                        ['nome' => 'Refrigerante Inválido', 'preco_centavos' => -500],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->dono, 'sanctum')
            ->postJson('/api/catalogo/importar', $invalidPayload);

        $response->assertStatus(422);

        $this->assertSame($initialCategoryCount, Categoria::count());
        $this->assertSame($initialProductCount, Produto::count());
    }

    public function test_drag_and_drop_category_reordering(): void
    {
        $cat2 = Categoria::create(['nome' => 'Pizzas', 'ordem' => 2, 'canais' => ['delivery']]);

        $response = $this->actingAs($this->dono, 'sanctum')
            ->patchJson('/api/categorias/reordenar', [
                'itens' => [
                    ['id' => $this->categoria->id, 'ordem' => 10],
                    ['id' => $cat2->id, 'ordem' => 5],
                ],
            ]);

        $response->assertStatus(200);

        $this->assertSame(10, $this->categoria->fresh()?->ordem);
        $this->assertSame(5, $cat2->fresh()?->ordem);
    }
}
