# WooCommerce ApusPayments #
**Tags:** woocommerce, apuspayments, payment, blockchain  
**Requires at least:** 4.0  
**Tested up to:** 4.9  
**Stable tag:** 2.13.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Adds ApusPayments gateway to the WooCommerce plugin

## Description ##

### Add ApusPayments gateway to WooCommerce ###

This plugin adds ApusPayments gateway to WooCommerce.

Please notice that WooCommerce must be installed and active.

### Compatibilidade ###

Compatível com versões posteriores ao WooCommerce 3.0.

### Instalação ###

Confira o nosso guia de instalação e configuração do ApusPayments na aba [Installation](http://wordpress.org/plugins/woocommerce-apuspayments/installation/).

### Integração ###

Este plugin funciona perfeitamente em conjunto com:

* [WooCommerce Multilingual](https://wordpress.org/plugins/woocommerce-multilingual/).

### Dúvidas? ###

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [Documentação](https://docs.apuspayments.com/).
* Utilizando o nosso [Github](https://github.com/apuspayments/WooComercePlugin).
* Criando um tópico no [fórum de ajuda do WordPress](http://wordpress.org/support/plugin/woocommerce-apuspayments).

## Installation ##

* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin;
* Navigate to WooCommerce -> Settings -> Payment Gateways, choose ApusPayments and fill in your ApusPayments vendor key and select wich blockchains you will accept.

### Instalação e configuração em Português: ###

### Instalação do plugin: ###

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.

### Requerimentos: ###

É necessário possuir uma conta no [ApusPayments](http://apuspayments.com/) e ter instalado o [WooCommerce](http://wordpress.org/plugins/woocommerce/).

### Configurações do Plugin: ###

Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Finalizar compra" > "ApusPayments".

1) Habilite o ApusPayments, adicione o vendor key do ApusPayments.

2) Selecione as blockchains que deseja receber pagamentos.

Pronto seus pagamentos já podem ser recebidos em criptomoedas.

## Frequently Asked Questions ##

### What is the plugin license? ###

* This plugin is released under a GPL license.

### What is needed to use this plugin? ###

* WooCommerce version 3.0 or latter installed and active.
* Only one account on [ApusPayments](http://apuspayments.com/ "ApusPayments").

### FAQ em Português: ###

### Qual é a licença do plugin? ###

Este plugin esta licenciado como GPL.

### O que eu preciso para utilizar este plugin? ###

* Ter instalado o plugin WooCommerce 3.0 ou mais recente.
* Possuir uma conta no ApusPayments.
* Gerar um vendor key ApusPayments.

### ApusPayments recebe pagamentos de quais países? ###

Confira as [Moedas](https://github.com/apuspayments/docs) disponíveis.

### Quais são as blockchains de pagamento que o plugin aceita? ###

Confira as [Blockchains](https://github.com/apuspayments/docs) disponíveis.

### Como que plugin faz integração com ApusPayments? ###

Fazemos a integração baseada na documentação oficial do ApusPayments que pode ser encontrada em "[Documentação](https://docs.apuspayments.com)" utilizando a última versão da API de pagamentos.

### O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto esta certo? ###

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

### Ao tentar finalizar a compra aparece a mensagem "ApusPayments: Um erro ocorreu ao processar o seu pagamento, por favor, tente novamente ou entre em contato para obter ajuda." o que fazer? ###

Esta mensagem geralmente aparece por causa que não foi configurado um **Vendor Key**.
Gere um novo vendor key no ApusPayments em "Preferências" > "[Integrações] e adicione ele nas configurações do plugin.

Note que caso você esteja utilizando a opção de **sandbox** é necessário usar um vendor key de testes que podem ser encontrados em "[ApusPayments > Sandbox > Dados de Teste]".

Se você tem certeza que o vendor key esta correto você deve acessar a página "WooCommerce > Status do Sistema" e verificar se **fsockopen** e **cURL** estão ativos. É necessário procurar ajuda do seu provedor de hospedagem caso você tenha o **fsockopen** e/ou o **cURL** desativados.

Por último é possível ativar a opção de **Log de depuração** nas configurações do plugin e tentar novamente fechar um pedido (você deve tentar fechar um pedido para que o log será gerado e o erro gravado nele).
Com o log é possível saber exatamente o que esta dando de errado com a sua instalação.

### O status do pedido não é alterado automaticamente? ###

Sim, o status é alterado automaticamente.

### Funciona com o Sandbox do ApusPayments? ###

Sim, funciona e basta você ativar isso nas opções do plugin.

### Mais dúvidas relacionadas ao funcionamento do plugin? ###


## Screenshots ##

### 1. Configurações do plugin. ###
![Configurações do plugin.](http://ps.w.org/woocommerce-apuspayments/assets/screenshot-1.png)

### 2. Método de pagamento na página de finalizar o pedido. ###
![Método de pagamento na página de finalizar o pedido.](http://ps.w.org/woocommerce-apuspayments/assets/screenshot-2.png)

### 3. Pagamento usando o Checkout Transparente. ###
![Pagamento com cartão de crédito usando o Checkout Transparente.](http://ps.w.org/woocommerce-apuspayments/assets/screenshot-4.png)

### 4. Pagamento recorrente usando o Checkout Transparente. ###
![Pagamento com debito online usando o Checkout Transparente.](http://ps.w.org/woocommerce-apuspayments/assets/screenshot-5.png)

## Changelog ##

### 1.0.0 - 2018/09/01 ###

* Plugin created.