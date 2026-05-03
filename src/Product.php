<?php

declare(strict_types=1);

namespace App;

/**
 * Product - Entidade principal do CRUD
 *
 * Representa um produto com operações básicas de validação.
 * Esta classe é o alvo dos testes unitários do Quadrante Q1.
 */
class Product
{
    private int $id;
    private string $name;
    private float $price;
    private int $stock;

    public function __construct(int $id, string $name, float $price, int $stock)
    {
        $this->setId($id);
        $this->setName($name);
        $this->setPrice($price);
        $this->setStock($stock);
    }

    // ─── Getters ────────────────────────────────────────────────────────────

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    // ─── Setters com validação ───────────────────────────────────────────────

    public function setId(int $id): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID must be a positive integer.');
        }
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Name cannot be empty.');
        }
        if (strlen($name) > 100) {
            throw new \InvalidArgumentException('Name cannot exceed 100 characters.');
        }
        $this->name = $name;
    }

    public function setPrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative.');
        }
        $this->price = $price;
    }

    public function setStock(int $stock): void
    {
        if ($stock < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative.');
        }
        $this->stock = $stock;
    }

    // ─── Lógica de negócio ───────────────────────────────────────────────────

    /**
     * Verifica se o produto está disponível em estoque.
     */
    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Aplica um desconto percentual ao preço do produto.
     *
     * @param float $percentage Percentual entre 0 e 100
     */
    public function applyDiscount(float $percentage): void
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Discount must be between 0 and 100.');
        }
        $this->price = round($this->price * (1 - $percentage / 100), 2);
    }

    /**
     * Decrementa o estoque ao realizar uma venda.
     *
     * @param int $quantity Quantidade a ser retirada do estoque
     */
    public function sell(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive.');
        }
        if ($quantity > $this->stock) {
            throw new \UnderflowException('Insufficient stock.');
        }
        $this->stock -= $quantity;
    }

    /**
     * Retorna os dados do produto como array associativo.
     */
    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
        ];
    }
}
