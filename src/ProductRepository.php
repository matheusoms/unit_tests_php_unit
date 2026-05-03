<?php

declare(strict_types=1);

namespace App;

/**
 * ProductRepository - Repositório em memória para operações CRUD
 *
 * Abstrai o acesso a dados dos produtos. Em produção esta classe
 * poderia ser substituída por uma implementação com banco de dados,
 * mantendo a mesma interface (princípio de inversão de dependência).
 */
class ProductRepository
{
    /** @var Product[] */
    private array $products = [];

    private int $nextId = 1;

    // ─── CREATE ──────────────────────────────────────────────────────────────

    /**
     * Cria e persiste um novo produto.
     *
     * @throws \InvalidArgumentException se nome, preço ou estoque forem inválidos
     */
    public function create(string $name, float $price, int $stock): Product
    {
        $product = new Product($this->nextId++, $name, $price, $stock);
        $this->products[$product->getId()] = $product;
        return $product;
    }

    // ─── READ ────────────────────────────────────────────────────────────────

    /**
     * Busca um produto pelo ID.
     *
     * @throws \RuntimeException se não encontrado
     */
    public function findById(int $id): Product
    {
        if (!isset($this->products[$id])) {
            throw new \RuntimeException("Product with ID {$id} not found.");
        }
        return $this->products[$id];
    }

    /**
     * Retorna todos os produtos cadastrados.
     *
     * @return Product[]
     */
    public function findAll(): array
    {
        return array_values($this->products);
    }

    /**
     * Retorna apenas produtos disponíveis (stock > 0).
     *
     * @return Product[]
     */
    public function findAvailable(): array
    {
        return array_values(
            array_filter($this->products, fn(Product $p) => $p->isAvailable())
        );
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    /**
     * Atualiza os dados de um produto existente.
     *
     * @throws \RuntimeException se não encontrado
     */
    public function update(int $id, string $name, float $price, int $stock): Product
    {
        $product = $this->findById($id);
        $product->setName($name);
        $product->setPrice($price);
        $product->setStock($stock);
        return $product;
    }

    // ─── DELETE ──────────────────────────────────────────────────────────────

    /**
     * Remove um produto pelo ID.
     *
     * @throws \RuntimeException se não encontrado
     */
    public function delete(int $id): void
    {
        $this->findById($id); // garante que existe
        unset($this->products[$id]);
    }

    // ─── Utilidades ──────────────────────────────────────────────────────────

    /**
     * Retorna a quantidade total de produtos cadastrados.
     */
    public function count(): int
    {
        return count($this->products);
    }
}
