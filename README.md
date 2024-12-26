# PIXCOIN

PIXCOIN é uma API responsável por alimentar o front de telemetrias e receber pagamentos.

## Funcionalidades

- **Telemetria**: Fornece dados em tempo real para o front-end.
- **Pagamentos**: Processa pagamentos de forma segura e eficiente.

## Tecnologias Utilizadas

- **PHP**: Linguagem de programação principal.
- **MySQL**: Banco de dados relacional.
- **Redis**: Armazenamento em cache.
- **Docker**: Contêineres para ambiente de desenvolvimento e produção.

## Instalação

1. Clone o repositório:
    ```sh
    git clone git@github.com:Luiz-Suvilao/PIXcoinAPI.git
    cd PIXcoinAPI
    ```

2. Instale as dependências:
    ```sh
    composer install
    ```

3. Configure o arquivo `.env`:
    ```sh
    cp .env.example .env
    php artisan key:generate
    ```

4. Configure o banco de dados no arquivo `.env` e execute as migrações:
    ```sh
    php artisan migrate
    ```

5. Inicie o servidor:
    ```sh
    php artisan serve
    ```
