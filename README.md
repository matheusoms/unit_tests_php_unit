# Documentação Técnica — CRUD PHP com Testes Unitários (Quadrante Q1)

> **Disciplina:** Qualidade e Testes de Software — Fatec Araras  
> **Professor:** Orlando Saraiva Júnior  
> **Tema:** Os Quadrantes do Teste Ágil — Quadrante Q1

---

## Sumário

1. [Contexto Teórico — Os Quadrantes do Teste Ágil](#1-contexto-teórico)
2. [Por que este projeto se enquadra no Quadrante Q1](#2-enquadramento-no-q1)
3. [Estrutura do Projeto](#3-estrutura-do-projeto)
4. [Código-fonte — `Product.php`](#4-produto-classe-product)
5. [Código-fonte — `ProductRepository.php`](#5-repositório-classe-productrepository)
6. [Testes Unitários — `ProductTest.php`](#6-testes-unitários-producttest)
7. [Testes de Componente — `ProductRepositoryTest.php`](#7-testes-de-componente-productrepositorytest)
8. [Pipeline CI/CD — GitHub Actions](#8-pipeline-cicd-github-actions)
9. [Como executar localmente](#9-como-executar-localmente)
10. [Interpretando os Resultados](#10-interpretando-os-resultados)
11. [Mapeamento para as Perguntas Norteadoras do Material](#11-perguntas-norteadoras)
12. [Referências](#12-referências)

---

## 1. Contexto Teórico

### 1.1 A Matriz dos Quadrantes do Teste Ágil

O material de aula apresenta a **Matriz dos Quadrantes de Testes Ágeis**, um modelo criado por Brian Marick e popularizado por Lisa Crispin e Janet Gregory nos livros *Agile Testing* e *More Agile Testing*.

A matriz divide os testes em dois eixos:

| Eixo | Valores |
|---|---|
| **Horizontal** | Technology-Facing ↔ Business-Facing |
| **Vertical** | Guide Development (Support the Team) ↔ Critique the Product |

Isso gera quatro quadrantes:

```
                     BUSINESS-FACING
                           │
          Q2               │               Q3
   Examples, A/B Tests     │   Exploratory, UAT,
   Story Tests, UX,        │   Usability Testing,
   Prototypes              │   Alpha/Beta
                           │
Guide ─────────────────────┼───────────────────── Critique
Development                │                      the Product
                           │
          Q1               │               Q4
   Unit Tests,             │   Performance Testing,
   Component Tests,        │   Security Testing,
   Testing Connectivity    │   Load Testing
                           │
                     TECHNOLOGY-FACING
```

### 1.2 O Quadrante Q1 — Technology-Facing / Guide Development

O **Q1** é o quadrante do **desenvolvimento orientado a testes (TDD)**. Segundo o material:

> *"O quadrante Q1 representa o desenvolvimento orientado a testes, que é uma prática central de desenvolvimento ágil."*

Características do Q1:

- São **testes voltados para o desenvolvedor** (technology-facing).
- **Guiam o desenvolvimento** — os testes são escritos antes ou junto com o código.
- Verificam a **qualidade interna** do código (conceito de Kent Beck).
- Devem ser **totalmente automatizados** e executados a cada alteração de código.
- Fornecem **feedback rápido** para que problemas sejam corrigidos imediatamente.
- Formam a **base de segurança** para refatoração (evitam regressões).

Tipos de teste que pertencem ao Q1:

| Tipo | O que verifica |
|---|---|
| **Unit Tests** | Funcionalidade de um único objeto ou método |
| **Component Tests** | Comportamento de um grupo de classes que fornecem um serviço |
| **Testing Connectivity** | Integração entre componentes internos |

---

## 2. Enquadramento no Q1

Este projeto implementa um CRUD de produtos em PHP e seus testes são **explicitamente do Quadrante Q1** pelos seguintes motivos:

### 2.1 São Technology-Facing

Os testes não foram escritos para serem lidos por analistas de negócio. Eles verificam **contratos técnicos** da implementação: se um ID negativo lança `InvalidArgumentException`, se o desconto é arredondado corretamente, se o repositório incrementa o ID automaticamente.

### 2.2 Guiam o Desenvolvimento

A estrutura dos testes **define o comportamento esperado** de cada método antes de pensar na implementação. Por exemplo, o teste abaixo "documenta" que o nome deve ser trimado antes de ser salvo:

```php
public function testTrimsNameWhitespace(): void
{
    $product = new Product(1, '  Notebook  ', 10.0, 1);
    $this->assertSame('Notebook', $product->getName()); // define o comportamento
}
```

### 2.3 São Automatizados

Todos os testes rodam com um único comando (`composer test`) e são executados automaticamente pelo GitHub Actions a cada `push` ou `pull_request`.

### 2.4 Permitem Medir a Qualidade Interna

O relatório de **cobertura de código** (coverage) mostra exatamente quais linhas de `Product.php` e `ProductRepository.php` foram exercitadas pelos testes — isso é a medição da qualidade interna descrita por Kent Beck.

---

## 3. Estrutura do Projeto

```
php-crud-q1/
├── .github/
│   └── workflows/
│       └── ci.yml              # Pipeline do GitHub Actions
├── src/
│   ├── Product.php             # Entidade com validações e lógica de negócio
│   └── ProductRepository.php  # Repositório CRUD em memória
├── tests/
│   ├── ProductTest.php         # Testes unitários da entidade
│   └── ProductRepositoryTest.php # Testes de componente do repositório
├── composer.json               # Dependências e scripts
├── phpunit.xml                 # Configuração do PHPUnit
└── .gitignore
```

---

## 4. Produto — Classe `Product`

### 4.1 Responsabilidade

Representa um produto do catálogo. Encapsula os dados e contém **regras de validação** (lança exceções para dados inválidos) e **lógica de negócio** (`applyDiscount`, `sell`, `isAvailable`).

### 4.2 Decisões de Design Guiadas pelos Testes (TDD)

| Decisão | Teste que a guiou |
|---|---|
| ID deve ser positivo | `testThrowsExceptionForZeroId` |
| Nome deve ser trimado | `testTrimsNameWhitespace` |
| Preço pode ser zero | `testCreatesProductWithZeroPrice` |
| Desconto arredonda com 2 decimais | `testDiscountRoundsToTwoDecimals` |
| Venda lança `UnderflowException` se insuficiente | `testThrowsExceptionWhenSellingMoreThanStock` |

### 4.3 Métodos e seus Testes

#### `__construct(int $id, string $name, float $price, int $stock)`

Invoca todos os setters com validação. Qualquer dado inválido lança exceção antes de o objeto ser criado.

#### `isAvailable(): bool`

```php
return $this->stock > 0;
```
Regra simples: produto com estoque zero não está disponível. Testado em:
- `testIsAvailableWhenStockIsPositive()`
- `testIsNotAvailableWhenStockIsZero()`

#### `applyDiscount(float $percentage): void`

```php
$this->price = round($this->price * (1 - $percentage / 100), 2);
```
O arredondamento em 2 casas decimais é crucial para evitar erros de ponto flutuante em valores monetários.

#### `sell(int $quantity): void`

```php
if ($quantity > $this->stock) {
    throw new \UnderflowException('Insufficient stock.');
}
$this->stock -= $quantity;
```
Protege contra vendas impossíveis. Usa `UnderflowException` (SPL) por ser semanticamente correto: a pilha (estoque) ficaria abaixo de zero.

---

## 5. Repositório — Classe `ProductRepository`

### 5.1 Responsabilidade

Abstrai o acesso e a persistência dos dados. Em produção, seria substituído por uma implementação com banco de dados (ex: `MysqlProductRepository`) sem mudar os testes — princípio da Inversão de Dependência.

### 5.2 Padrão Repository

```
ProductRepository (interface implícita)
    ├── create()     → gera ID auto-incremental
    ├── findById()   → busca por ID, lança se não encontrado
    ├── findAll()    → retorna todos
    ├── findAvailable() → filtra por stock > 0
    ├── update()     → delega para os setters de Product
    ├── delete()     → remove do array interno
    └── count()      → retorna tamanho do array
```

### 5.3 Isolamento nos Testes

Cada teste do `ProductRepositoryTest` recebe um repositório **limpo e independente** graças ao método `setUp()`:

```php
protected function setUp(): void
{
    $this->repo = new ProductRepository(); // estado zerado antes de cada teste
}
```

Isso garante que os testes não interferem entre si — princípio fundamental dos testes unitários/componente do Q1.

---

## 6. Testes Unitários — `ProductTest`

### 6.1 Organização por Comportamento

Os testes são agrupados logicamente por método ou comportamento:

```
ProductTest
├── Criação válida (3 testes)
├── Validações de ID (2 testes)
├── Validações de Nome (5 testes)
├── Validações de Preço (1 teste)
├── Validações de Estoque (1 teste)
├── isAvailable() (2 testes)
├── applyDiscount() (6 testes)
├── sell() (5 testes)
└── toArray() (1 teste)
```

### 6.2 Padrão de Teste de Exceção

Para verificar que exceções são lançadas corretamente, o PHPUnit oferece dois métodos:

```php
public function testThrowsExceptionForEmptyName(): void
{
    // Declara ANTES do código que provoca a exceção
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Name cannot be empty.');
    
    // Esta linha deve lançar a exceção declarada acima
    new Product(1, '', 10.0, 1);
}
```

### 6.3 Cobertura de Casos Limite (Boundary Testing)

Bons testes unitários verificam não apenas o "caminho feliz", mas os **limites**:

| Caso | Teste |
|---|---|
| Nome com exatamente 100 chars (limite superior válido) | `testAcceptsNameWithExactly100Chars` |
| Nome com 101 chars (limite superior inválido) | `testThrowsExceptionForNameExceeding100Chars` |
| Desconto = 0 (mínimo válido) | `testAppliesZeroDiscount` |
| Desconto = 100 (máximo válido) | `testAppliesFullDiscount` |
| Preço = 0 (zero válido) | `testCreatesProductWithZeroPrice` |

---

## 7. Testes de Componente — `ProductRepositoryTest`

### 7.1 Diferença entre Unitário e Componente no Q1

| Aspecto | Teste Unitário | Teste de Componente |
|---|---|---|
| Escopo | Um único método/objeto | Grupo de classes trabalhando juntas |
| Exemplo aqui | `ProductTest` | `ProductRepositoryTest` |
| O que valida | Lógica interna de `Product` | Interação entre `Product` + `ProductRepository` |

### 7.2 Cenário de Ciclo Completo

O teste `testFullCrudCycle()` valida todo o fluxo CRUD em sequência:

```php
public function testFullCrudCycle(): void
{
    // Create
    $product = $this->repo->create('Widget', 25.0, 100);
    $this->assertSame(1, $this->repo->count());

    // Read
    $found = $this->repo->findById($product->getId());
    $this->assertSame('Widget', $found->getName());

    // Update
    $this->repo->update($product->getId(), 'Super Widget', 30.0, 150);
    $this->assertSame('Super Widget', $this->repo->findById($product->getId())->getName());

    // Delete
    $this->repo->delete($product->getId());
    $this->assertSame(0, $this->repo->count());
}
```

---

## 8. Pipeline CI/CD — GitHub Actions

### 8.1 Visão Geral do Workflow

O arquivo `.github/workflows/ci.yml` define dois **jobs** que rodam em paralelo:

```
CI Pipeline
├── unit-tests (matrix: PHP 8.1, 8.2, 8.3)
│   ├── Checkout
│   ├── Setup PHP + Xdebug
│   ├── Cache Composer
│   ├── Install dependencies
│   ├── Run PHPUnit + coverage
│   ├── Publish JUnit report (GitHub Summary)
│   └── Upload coverage artifact (só PHP 8.2)
└── code-quality
    ├── Checkout
    ├── Setup PHP
    └── PHP Lint (verificação de sintaxe)
```

### 8.2 Triggers (Gatilhos)

```yaml
on:
  push:
    branches: [ "main", "develop" ]
  pull_request:
    branches: [ "main", "develop" ]
```

O pipeline roda automaticamente quando:
- Há um `push` direto para `main` ou `develop`
- Um Pull Request é aberto ou atualizado contra essas branches

### 8.3 Matrix Strategy — Testando em Múltiplas Versões

```yaml
strategy:
  fail-fast: false
  matrix:
    php-version: ["8.1", "8.2", "8.3"]
```

- **`fail-fast: false`** — mesmo que PHP 8.1 falhe, os testes em 8.2 e 8.3 continuam.
- A matriz garante que o código é compatível com **todas as versões suportadas de PHP**.

### 8.4 Cache de Dependências

```yaml
- name: Cache Composer dependencies
  uses: actions/cache@v4
  with:
    path: vendor
    key: composer-${{ matrix.php-version }}-${{ hashFiles('composer.lock') }}
```

A chave do cache inclui a **versão do PHP** e o **hash do `composer.lock`**. Isso significa:
- O cache é invalidado automaticamente quando o `composer.lock` muda.
- Cada versão do PHP tem seu próprio cache separado.

### 8.5 Relatório de Cobertura

O PHPUnit é executado com as flags de cobertura:

```bash
vendor/bin/phpunit \
  --coverage-text \          # exibe no terminal
  --coverage-clover=coverage/clover.xml \  # formato para ferramentas externas
  --log-junit=test-results/junit.xml       # formato para GitHub Actions
```

O relatório `clover.xml` pode ser integrado com serviços como **Codecov** ou **SonarCloud** para acompanhamento histórico.

### 8.6 Job Summary no GitHub

O step `Show coverage summary` escreve no arquivo especial `$GITHUB_STEP_SUMMARY`, que aparece na aba **Summary** da execução do workflow no GitHub:

```yaml
- name: Show coverage summary
  run: |
    echo "## Coverage Summary" >> $GITHUB_STEP_SUMMARY
    vendor/bin/phpunit --coverage-text --colors=never 2>&1 | \
      grep -A 30 "Code Coverage Report" >> $GITHUB_STEP_SUMMARY || true
```

---

## 9. Como Executar Localmente

### 9.1 Pré-requisitos

- PHP >= 8.1
- Composer >= 2.x
- Extensão Xdebug (para cobertura de código)

### 9.2 Instalação

```bash
# Clone o repositório
git clone <seu-repositorio>
cd php-crud-q1

# Instale as dependências
composer install
```

### 9.3 Executando os Testes

```bash
# Execução padrão
composer test

# Com saída detalhada (nome de cada teste)
composer test:verbose

# Com relatório de cobertura de código
composer test:coverage

# Ou diretamente com PHPUnit
vendor/bin/phpunit
```

### 9.4 Saída Esperada

```
PHPUnit 10.x.x

.......................................               35 / 35 (100%)

Time: 00:00.020, Memory: 8.00 MB

OK (35 tests, 62 assertions)

Code Coverage Report:
  2024-01-01 00:00:00

 Summary:
  Classes: 100.00% (2/2)
  Methods: 100.00% (17/17)
  Lines:   100.00% (XX/XX)
```

---

## 10. Interpretando os Resultados

### 10.1 O que cada resultado significa

| Resultado | Significado |
|---|---|
| ✅ `OK (35 tests, 62 assertions)` | Todos os testes passaram, nenhum comportamento inesperado |
| ❌ `FAILED` | Algum comportamento esperado não ocorreu — o código tem um bug ou o teste está desatualizado |
| ⚠️ `WARNING` | Algo suspeito mas não necessariamente errado (ex: teste sem assertions) |
| 💀 `ERROR` | Exceção não esperada foi lançada durante o teste |

### 10.2 Lendo o Relatório de Cobertura

```
 Summary:
  Classes: 100.00% (2/2)   ← todas as classes têm pelo menos 1 teste
  Methods: 100.00% (17/17) ← todos os métodos foram chamados
  Lines:   97.00% (XX/XX)  ← 3% das linhas não foram executadas pelos testes
```

> **Atenção:** 100% de cobertura **não** significa que o código está livre de bugs. Significa apenas que cada linha foi executada ao menos uma vez. É um indicador de qualidade, não uma garantia.

### 10.3 Relatório JUnit no GitHub Actions

Após cada execução do workflow, a aba **Summary** do GitHub exibe:

- Total de testes executados
- Total de falhas
- Tempo de execução
- Lista de testes que falharam (com mensagem de erro)

---

## 11. Perguntas Norteadoras

O material de aula propõe as seguintes perguntas para reflexão. Aqui está como este projeto responde a cada uma:

### "Estamos usando testes unitários e de componentes para nos ajudar a encontrar o design certo para nossa aplicação?"

**Sim.** Os testes de `Product` revelaram a necessidade de:
- Trimagem do nome no setter (sem o teste, poderíamos esquecer).
- Separação clara entre `InvalidArgumentException` (dado errado) e `UnderflowException` (condição de negócio impossível).
- Método `toArray()` como interface pública de leitura.

### "Temos um processo de construção automatizado que executa nossos testes de unidade automatizados para feedback rápido?"

**Sim.** O GitHub Actions executa todos os 35 testes automaticamente a cada push, em menos de 30 segundos.

### "Nossos testes voltados para os negócios nos ajudam a entregar um produto que atenda às expectativas dos clientes?"

Este projeto é **Q1 puro** (technology-facing). Os testes de negócio (Q2, Q3) seriam representados por testes de aceitação como Behat/Cucumber, que não fazem parte do escopo deste exercício.

### "Consideramos requisitos tecnológicos como desempenho e segurança suficientemente cedo no ciclo de desenvolvimento?"

Os testes do **Q4** (performance, segurança) são complementares ao Q1 e não estão incluídos aqui — mas a estrutura de projeto (repositório separado, validação por exceções) facilita a adição futura de testes de carga e segurança.

---

## 12. Referências

- Crispin, L. & Gregory, J. *Agile Testing: A Practical Guide for Testers and Agile Teams*. Addison-Wesley, 2009.
- Gregory, J. & Crispin, L. *More Agile Testing: Learning Journeys for the Whole Team*. Addison-Wesley, 2014.
- Beck, K. *Test Driven Development: By Example*. Addison-Wesley, 2002.
- [PHPUnit Documentation](https://docs.phpunit.de/en/10.5/)
- [GitHub Actions — Workflow syntax](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
- Material de aula: *Os Quadrantes do Teste Ágil* — Prof. Me. Orlando Saraiva Júnior, Fatec Araras.
