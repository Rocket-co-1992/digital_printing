# README.md

# Gráfica Digital - Gestão de Tarefas e Orçamentos

Este projeto é uma aplicação web para a gestão de tarefas e orçamentos de uma gráfica digital. A aplicação inclui um sistema de Kanban, gestão de clientes e empresas, cálculo de orçamentos, controle de stock, gestão de impressoras e máquinas de design digital, e envio automático de orçamentos por e-mail.

## Funcionalidades Principais

1. **Kanban Automatizado**
   - Interface drag-and-drop para mover tarefas entre colunas.
   - Atribuição de funcionários a tarefas.
   - Tempo estimado de conclusão e alertas de atraso.
   - Redistribuição automática de tarefas se houver atrasos.
   - Integração com orçamentos.

2. **Gestão de Clientes e Empresas**
   - Registro de clientes individuais e empresas.
   - Associação de múltiplos clientes a uma empresa.
   - Registro de dados de faturação.
   - Histórico de pedidos e orçamentos.

3. **Gestão de Impressoras e Máquinas**
   - Cadastro de impressoras e equipamentos gráficos.
   - Monitorização do uso e necessidade de manutenção.

4. **Gestão de Stock**
   - Gestão de materiais gráficos.
   - Atualização automática do stock conforme o uso na produção.

5. **Calculadora de Orçamentos**
   - Geração automática de custos internos e preços finais.

6. **Criação e Gestão de Orçamentos**
   - Criação de orçamentos detalhados e personalizáveis.
   - Envio automático de orçamentos por e-mail.

7. **Relatórios e Estatísticas**
   - Relatórios mensais sobre vendas, stock, clientes e orçamentos rejeitados.

## Stack Tecnológica

- **Frontend:** React com TypeScript, Bootstrap.
- **Backend:** Node.js com Express e PHP 8 com Twig.
- **Banco de Dados:** MongoDB e MySQL.
- **Infraestrutura:** Servidor Linux próprio.

## Como Começar

1. Clone o repositório.
2. Navegue até a pasta `client` e execute `npm install` para instalar as dependências do frontend.
3. Navegue até a pasta `server` e execute `npm install` para instalar as dependências do backend.
4. Execute `docker-compose up` para iniciar os serviços.

## Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou pull requests.

## Licença

Este projeto está licenciado sob a MIT License.