<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Support\Security\BlindIndexService;
use App\Support\Security\SensitiveDataRedactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class SecurityEncryptionTest extends TestCase
{
    use RefreshDatabase;

    private BlindIndexService $blindIndex;
    private SensitiveDataRedactor $redactor;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.blind_index_key' => 'test-blind-index-key-secret-32-chars-long!']);
        $this->blindIndex = new BlindIndexService('test-blind-index-key-secret-32-chars-long!');
        $this->redactor = new SensitiveDataRedactor();
    }

    public function test_sensitive_fields_are_stored_encrypted_in_database(): void
    {
        $plano = Plano::create([
            'nome' => 'delivery',
            'preco_centavos' => 9990,
            'recursos_json' => ['cardapio_online' => true],
            'limite_usuarios' => 2,
        ]);

        $rawCnpj = '11.222.333/0001-44';
        $rawToken = 'TEST_MP_ACCESS_TOKEN_XYZ_SECRET';

        $loja = Loja::create([
            'slug' => 'loja-segura',
            'nome' => 'Loja Segura Ltda',
            'cnpj_cpf' => $rawCnpj,
            'cnpj_cpf_bidx' => $this->blindIndex->generateForDocument($rawCnpj),
            'telefone_whatsapp' => '31999990000',
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'mp_access_token' => $rawToken,
        ]);

        // Raw DB query to verify ciphertext in MySQL
        $rawRecord = DB::table('lojas')->where('id', $loja->id)->first();
        $this->assertNotNull($rawRecord);

        // Raw columns must NOT contain plaintext values
        $this->assertNotEquals($rawCnpj, $rawRecord->cnpj_cpf);
        $this->assertNotEquals($rawToken, $rawRecord->mp_access_token);
        $this->assertStringNotContainsString($rawToken, (string) $rawRecord->mp_access_token);

        // Model attributes decrypt seamlessly
        $this->assertSame($rawCnpj, $loja->cnpj_cpf);
        $this->assertSame($rawToken, $loja->mp_access_token);

        // Serialization check: model hidden attributes never leak in toArray() or JSON
        $array = $loja->toArray();
        $this->assertArrayNotHasKey('mp_access_token', $array);
        $this->assertArrayNotHasKey('mp_refresh_token', $array);
        $this->assertArrayNotHasKey('cnpj_cpf', $array);
        $this->assertArrayNotHasKey('cnpj_cpf_bidx', $array);

        $json = json_encode($loja);
        $this->assertStringNotContainsString('TEST_MP_ACCESS_TOKEN_XYZ_SECRET', (string) $json);
        $this->assertStringNotContainsString($rawCnpj, (string) $json);
    }

    public function test_blind_index_generation_is_deterministic_and_normalized(): void
    {
        $phone1 = '+55 (31) 98765-4321';
        $phone2 = '31987654321';
        $phone3 = '5531987654321';

        $bidx1 = $this->blindIndex->generateForPhone($phone1);
        $bidx2 = $this->blindIndex->generateForPhone($phone2);
        $bidx3 = $this->blindIndex->generateForPhone($phone3);

        $this->assertSame($bidx1, $bidx2);
        $this->assertSame($bidx2, $bidx3);
        $this->assertSame(64, strlen($bidx1)); // SHA256 hex string length
    }

    public function test_sensitive_data_redactor_scrubs_all_credentials(): void
    {
        $payload = [
            'nome' => 'Fulano de Tal',
            'senha' => 'plain_text_password',
            'mp_access_token' => 'APP_USR-xxxx-xxxx',
            'nested' => [
                'token' => 'secret_jwt_token',
                'pix_copia_cola' => '00020126580014br.gov.bcb.pix...',
                'safe_field' => 'Public Value',
            ],
        ];

        $redacted = $this->redactor->redact($payload);

        $this->assertSame('Fulano de Tal', $redacted['nome']);
        $this->assertSame('[REDACTED]', $redacted['senha']);
        $this->assertSame('[REDACTED]', $redacted['mp_access_token']);
        $this->assertSame('[REDACTED]', $redacted['nested']['token']);
        $this->assertSame('[REDACTED]', $redacted['nested']['pix_copia_cola']);
        $this->assertSame('Public Value', $redacted['nested']['safe_field']);
    }
}
