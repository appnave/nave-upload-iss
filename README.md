# Nave Upload ISS

Pacote privado da App Nave para gerar URLs pré-assinadas de upload em S3 em projetos Laravel. O consumo é feito via Composer com repositório VCS no projeto cliente.

## Visão geral

- Nome do pacote: `appnave/nave-upload-iss`
- Namespace principal: `Bildvitta\IssUpload`
- Rotas padrão: `POST /api/upload-credentials` e `POST /api/upload`
- Upload baseado no disk `s3` do Laravel

## Requisitos

- PHP 8.0+
- Laravel compatível com `illuminate/contracts` 8, 9, 10, 11 ou 12
- Disk `s3` configurado no projeto cliente
- Credenciais AWS válidas
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

## Instalação local

Publique a configuração do pacote:

```bash
php artisan vendor:publish --provider="Bildvitta\IssUpload\IssUploadServiceProvider" --tag="iss-upload-config"
```

Configure o `.env` do projeto consumidor:

```dotenv
ISS_IMAGE_ROUTE_PREFIX=api
ISS_IMAGE_ROUTE_MIDDLEWARE=hub.auth
ISS_IMAGE_VALID_MIME_TYPE="image/jpeg,image/gif,image/bmp,image/tiff,image/png,application/pdf"

AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=...
```

Observação:

- O middleware padrão é `hub.auth`; ajuste essa configuração no projeto cliente se ele usar outro middleware.
- O `mime_type` é inferido pela extensão do arquivo e validado pela configuração `ISS_IMAGE_VALID_MIME_TYPE`.

Exemplo de uso local:

```bash
curl --location --request POST 'http://127.0.0.1:8000/api/upload-credentials' \
  --header 'Content-Type: application/json' \
  --data-raw '{
    "filename": "arquivo.png",
    "entity": "realEstateDevelopmentsMedias"
  }'
```

## Useful Commands

- `php artisan vendor:publish --provider="Bildvitta\IssUpload\IssUploadServiceProvider" --tag="iss-upload-config"`
- `php artisan iss-upload`
- `composer test`
- `composer test-coverage`
- `composer check-style`
- `composer fix-style`
- `composer psalm`

## Informações adicionais

- Se precisar de uma rota própria, use `Bildvitta\IssUpload\Http\Controllers\UploadController`.
- A resposta do upload contém `path`, `full_path` e `endpoint`.
- A documentação de mudanças está em [CHANGELOG.md](CHANGELOG.md).
- As orientações de contribuição estão em [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md).
- A política de segurança está em [.github/SECURITY.md](.github/SECURITY.md).
- A licença está em [LICENSE.md](LICENSE.md).
