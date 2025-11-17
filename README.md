# TidyUp

Este projeto utiliza Docker para criar um ambiente de desenvolvimento com múltiplos serviços.

## Estrutura do Docker

O ambiente é orquestrado pelo `docker-compose.yml` e é composto pelos seguintes serviços:

- `app`: Contêiner com a aplicação **PHP 8.2-FPM**. O código-fonte deve ser colocado no diretório `backend/`.
- `angular`: Contêiner com **Node.js 18.13.0** e **Angular CLI 15.2.9**. O código-fonte deve ser colocado no diretório `frontend/`.
- `nginx`: Servidor web **Nginx** que atua como proxy reverso para as aplicações `app` e `angular`. A configuração está em `nginx/conf.d/`.
- `db`: Banco de dados **MySQL 8.0**. Os dados são persistidos no volume `dbdata`.
- `redis`: Servidor **Redis** para cache ou filas.

## Como Executar o Ambiente

Certifique-se de ter o Docker e o Docker Compose instalados em sua máquina.

### 1. Construir as Imagens

Na primeira vez que for executar o projeto, ou após fazer alterações nos `Dockerfiles`, você precisa construir as imagens dos serviços:

```bash
docker-compose build
```

### 2. Iniciar os Serviços

Para iniciar todos os serviços em modo "detached" (em segundo plano):

```bash
docker-compose up -d
```

A aplicação estará acessível em `http://localhost` (ou no endereço que você configurar no Nginx).

### 3. Parar os Serviços

Para parar todos os serviços:

```bash
docker-compose down
```

### 4. Acessar um Contêiner

Para acessar o terminal (bash) de um serviço em execução, utilize o comando `exec`. Por exemplo, para acessar o contêiner da aplicação PHP:

```bash
docker-compose exec api bash
```

Ou para acessar o contêiner da aplicação Angular:

```bash
docker-compose exec angular bash
```

### 5. Visualizar Logs

Para visualizar os logs de todos os serviços em tempo real:

```bash
docker-compose logs -f
```

Para visualizar os logs de um serviço específico:

```bash
docker-compose logs -f <nome_do_servico>
```
