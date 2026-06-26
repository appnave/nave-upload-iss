# Nave Upload ISS

Pacote privado da App Nave para gerar URLs pré-assinadas de upload em storages compatíveis com S3 em projetos Laravel. O consumo é feito via Composer com repositório VCS no projeto cliente.

## Visão geral

- Nome do pacote: `appnave/nave-upload-iss`
- Namespace principal: `Bildvitta\IssUpload`
- Rota padrão: `POST /api/upload-credentials`
- Suporte a qualquer storage compatível com S3 (AWS S3, MinIO, DigitalOcean Spaces, Cloudflare R2, etc.)

## Requisitos

- PHP 8.0+
- Laravel compatível com `illuminate/contracts` 8, 9, 10, 11 ou 12
- Disk S3-compatible configurado no projeto cliente
- Acesso ao repositório privado no GitHub

## Acesso aos repositórios privados

No projeto cliente, adicione o repositório VCS em `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ORG/REPO"
    }
  ]
}
```

Depois instale o pacote:

```bash
composer require appnave/nave-upload-iss
```

Autenticação local do Composer com token GitHub:

```bash
composer config -g github-oauth.github.com <YOUR_TOKEN>
```

GitHub Actions:

```yaml
env:
  COMPOSER_AUTH: >-
    {"github-oauth":{"github.com":"${{ secrets.GH_TOKEN }}"}}
```

## Instalação

### 1. Publicar a configuração

Use o comando inteligente de publicação, que adiciona apenas as chaves ausentes caso o arquivo já exista:

```bash
php artisan iss-upload:publish-config
```

Ou publique via `vendor:publish` para sobrescrever o arquivo inteiro:

```bash
php artisan vendor:publish --provider="Bildvitta\IssUpload\IssUploadServiceProvider" --tag="iss-upload-config"
```

### 2. Configurar o `.env`

```dotenv
# Storage
ISS_UPLOAD_DISK=s3                    # nome do disk Laravel (padrão: s3)
ISS_UPLOAD_ACL=public-read            # ACL do objeto no S3 (padrão: public-read)
ISS_UPLOAD_PRESIGNED_EXPIRY=+10 minutes  # validade da URL pré-assinada (padrão: +10 minutes)
ISS_UPLOAD_PATH_PREFIX=uploads        # prefixo do caminho no bucket (padrão: uploads)

# Rotas
ISS_IMAGE_ROUTE_PREFIX=api
ISS_IMAGE_ROUTE_MIDDLEWARE=hub.auth

# Validação
ISS_IMAGE_VALID_MIME_TYPE="image/jpeg,image/gif,image/bmp,image/tiff,image/png,application/pdf"

# Credenciais AWS (ou equivalente do seu provider)
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=...
```

> O middleware padrão é `hub.auth`; ajuste conforme o projeto cliente.  
> O `mime_type` é inferido automaticamente pela extensão do arquivo.

## Usando um storage alternativo (MinIO, R2, Spaces…)

Configure o disk desejado em `config/filesystems.php` do projeto cliente apontando para o endpoint do seu provider. Em seguida, defina `ISS_UPLOAD_DISK` com o nome desse disk:

```dotenv
ISS_UPLOAD_DISK=minio
```

```php
// config/filesystems.php
'minio' => [
    'driver'   => 's3',
    'key'      => env('MINIO_ACCESS_KEY_ID'),
    'secret'   => env('MINIO_SECRET_ACCESS_KEY'),
    'region'   => env('MINIO_DEFAULT_REGION', 'us-east-1'),
    'bucket'   => env('MINIO_BUCKET'),
    'endpoint' => env('MINIO_ENDPOINT'),
    'use_path_style_endpoint' => true,
],
```

## Resposta da API

`POST /api/upload-credentials`

**Request:**

```json
{
  "filename": "arquivo.png",
  "entity": "realEstateDevelopmentsMedias"
}
```

**Response `200`:**

```json
{
  "path": "uploads/realEstateDevelopmentsMedias/arquivo.png",
  "full_path": "https://bucket.s3.amazonaws.com/uploads/realEstateDevelopmentsMedias/arquivo.png",
  "endpoint": "https://bucket.s3.amazonaws.com/uploads/realEstateDevelopmentsMedias/arquivo.png?X-Amz-Algorithm=..."
}
```

| Campo | Descrição |
|---|---|
| `path` | Chave do objeto no bucket |
| `full_path` | URL pública do arquivo após o upload |
| `endpoint` | URL pré-assinada para o upload direto (válida por `ISS_UPLOAD_PRESIGNED_EXPIRY`) |

## Comandos Artisan

| Comando | Descrição |
|---|---|
| `php artisan iss-upload:publish-config` | Publica o config adicionando apenas chaves ausentes se o arquivo já existir |
| `php artisan iss-upload:check` | Exibe a configuração atual e testa a conectividade com o storage |

## Outros comandos

```bash
composer test
composer test-coverage
composer check-style
composer fix-style
composer psalm
```

## Informações adicionais

- Se precisar de uma rota própria, use `Bildvitta\IssUpload\Http\Controllers\UploadController`.
- A documentação de mudanças está em [CHANGELOG.md](CHANGELOG.md).
- As orientações de contribuição estão em [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md).
- A política de segurança está em [.github/SECURITY.md](.github/SECURITY.md).
- A licença está em [LICENSE.md](LICENSE.md).
