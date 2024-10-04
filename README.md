# PixWave

**PixWave** é um sistema de microserviços desenvolvido em PHP com o framework Laravel, projetado para processar pagamentos via PIX utilizando dois gateways de pagamento diferentes. O sistema escolhe automaticamente o gateway mais disponível com base em monitoramento em tempo real, garantindo alta disponibilidade e mecanismos de fallback. O sistema utiliza Redis para cache, SQL e SNS para mensageria, e a arquitetura é baseada em Docker e Kubernetes para orquestração. Os logs são centralizados com Logstash e Elasticsearch para facilitar o monitoramento.

## Tecnologias Utilizadas

- **PHP** 
- **MySQL** (para armazenamento de transações)
- **Redis** (para cache)
- **SNS (Amazon Simple Notification Service)** (para mensageria assíncrona)
- **Docker** (para conteinerização)
- **Kubernetes** (para orquestração de containers)
- **Logstash e Elasticsearch** (para centralização e monitoramento de logs)
- **Laravel Sanctum** (para autenticação de API)

## Funcionalidades

- **Processamento de Pagamentos PIX**: Cria e processa pagamentos via PIX usando dois gateways de pagamento com lógica de fallback.
- **Fallback Automático**: Se o primeiro gateway estiver indisponível, o sistema tentará automaticamente outro gateway.
- **Histórico de Transações**: Registra todas as transações de entrada e saída em um banco de dados MySQL.
- **Mensageria**: Integração com SNS para envio de notificações assíncronas.
- **Cache**: Utiliza Redis para armazenar status dos gateways e informações frequentemente acessadas.
- **Orquestração com Docker e Kubernetes**: O sistema é totalmente conteinerizado para facilitar a escalabilidade e resiliência.
- **Logs Centralizados**: Logstash e Elasticsearch são usados para capturar, processar e monitorar logs em tempo real.

## Requisitos

- PHP 8.0+
- Composer
- Docker
- Redis
- MySQL
- Elasticsearch & Logstash (opcional)
- SNS (Amazon Simple Notification Service)
