<?php

declare(strict_types=1);

namespace Tests;

use App\Product;
use PHPUnit\Framework\TestCase;

/**
 * ProductTest — Testes unitários para a entidade Product
 *
 * Quadrante Q1 (Technology-Facing / Guide Development):
 *   - Testes de unidade que verificam a funcionalidade de um único objeto
 *   - Orientam o design interno da classe (TDD)
 *   - São automatizados e fornecem feedback rápido a cada build
 */
class ProductTest extends TestCase
{
    // ════════════════════════════════════════════════════════════════════════
    // Criação válida
    // ════════════════════════════════════════════════════════════════════════

    public function testCreatesProductWithValidData(): void
    {
        $product = new Product(1, 'Notebook', 3500.00, 10);

        $this->assertSame(1, $product->getId());
        $this->assertSame('Notebook', $product->getName());
        $this->assertSame(3500.00, $product->getPrice());
        $this->assertSame(10, $product->getStock());
    }

    public function testCreatesProductWithZeroPrice(): void
    {
        $product = new Product(1, 'Free Item', 0.0, 5);
        $this->assertSame(0.0, $product->getPrice());
    }

    public function testCreatesProductWithZeroStock(): void
    {
        $product = new Product(1, 'Out of Stock', 99.99, 0);
        $this->assertSame(0, $product->getStock());
    }

    // ════════════════════════════════════════════════════════════════════════
    // Validações de ID
    // ════════════════════════════════════════════════════════════════════════

    public function testThrowsExceptionForZeroId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID must be a positive integer.');
        new Product(0, 'Item', 10.0, 1);
    }

    public function testThrowsExceptionForNegativeId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Product(-1, 'Item', 10.0, 1);
    }

    // ════════════════════════════════════════════════════════════════════════
    // Validações de Nome
    // ════════════════════════════════════════════════════════════════════════

    public function testThrowsExceptionForEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name cannot be empty.');
        new Product(1, '', 10.0, 1);
    }

    public function testThrowsExceptionForWhitespaceName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Product(1, '   ', 10.0, 1);
    }

    public function testThrowsExceptionForNameExceeding100Chars(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name cannot exceed 100 characters.');
        new Product(1, str_repeat('a', 101), 10.0, 1);
    }

    public function testAcceptsNameWithExactly100Chars(): void
    {
        $product = new Product(1, str_repeat('a', 100), 10.0, 1);
        $this->assertSame(100, strlen($product->getName()));
    }

    public function testTrimsNameWhitespace(): void
    {
        $product = new Product(1, '  Notebook  ', 10.0, 1);
        $this->assertSame('Notebook', $product->getName());
    }

    // ════════════════════════════════════════════════════════════════════════
    // Validações de Preço
    // ════════════════════════════════════════════════════════════════════════

    public function testThrowsExceptionForNegativePrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price cannot be negative.');
        new Product(1, 'Item', -1.0, 1);
    }

    // ════════════════════════════════════════════════════════════════════════
    // Validações de Estoque
    // ════════════════════════════════════════════════════════════════════════

    public function testThrowsExceptionForNegativeStock(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock cannot be negative.');
        new Product(1, 'Item', 10.0, -1);
    }

    // ════════════════════════════════════════════════════════════════════════
    // isAvailable()
    // ════════════════════════════════════════════════════════════════════════

    public function testIsAvailableWhenStockIsPositive(): void
    {
        $product = new Product(1, 'Item', 10.0, 5);
        $this->assertTrue($product->isAvailable());
    }

    public function testIsNotAvailableWhenStockIsZero(): void
    {
        $product = new Product(1, 'Item', 10.0, 0);
        $this->assertFalse($product->isAvailable());
    }

    // ════════════════════════════════════════════════════════════════════════
    // applyDiscount()
    // ════════════════════════════════════════════════════════════════════════

    public function testAppliesDiscountCorrectly(): void
    {
        $product = new Product(1, 'Item', 100.00, 1);
        $product->applyDiscount(10);
        $this->assertSame(90.00, $product->getPrice());
    }

    public function testAppliesZeroDiscount(): void
    {
        $product = new Product(1, 'Item', 100.00, 1);
        $product->applyDiscount(0);
        $this->assertSame(100.00, $product->getPrice());
    }

    public function testAppliesFullDiscount(): void
    {
        $product = new Product(1, 'Item', 100.00, 1);
        $product->applyDiscount(100);
        $this->assertSame(0.00, $product->getPrice());
    }

    public function testDiscountRoundsToTwoDecimals(): void
    {
        $product = new Product(1, 'Item', 9.99, 1);
        $product->applyDiscount(15);
        $this->assertSame(8.49, $product->getPrice());
    }

    public function testThrowsExceptionForNegativeDiscount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount must be between 0 and 100.');
        $product = new Product(1, 'Item', 100.00, 1);
        $product->applyDiscount(-1);
    }

    public function testThrowsExceptionForDiscountOver100(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $product = new Product(1, 'Item', 100.00, 1);
        $product->applyDiscount(101);
    }

    // ════════════════════════════════════════════════════════════════════════
    // sell()
    // ════════════════════════════════════════════════════════════════════════

    public function testSellDecreasesStock(): void
    {
        $product = new Product(1, 'Item', 10.0, 10);
        $product->sell(3);
        $this->assertSame(7, $product->getStock());
    }

    public function testSellAllStock(): void
    {
        $product = new Product(1, 'Item', 10.0, 5);
        $product->sell(5);
        $this->assertSame(0, $product->getStock());
        $this->assertFalse($product->isAvailable());
    }

    public function testThrowsExceptionWhenSellingMoreThanStock(): void
    {
        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('Insufficient stock.');
        $product = new Product(1, 'Item', 10.0, 3);
        $product->sell(5);
    }

    public function testThrowsExceptionForZeroQuantitySell(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be positive.');
        $product = new Product(1, 'Item', 10.0, 5);
        $product->sell(0);
    }

    public function testThrowsExceptionForNegativeQuantitySell(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $product = new Product(1, 'Item', 10.0, 5);
        $product->sell(-1);
    }

    // ════════════════════════════════════════════════════════════════════════
    // toArray()
    // ════════════════════════════════════════════════════════════════════════

    public function testToArrayReturnsCorrectStructure(): void
    {
        $product = new Product(1, 'Notebook', 3500.00, 10);
        $expected = [
            'id'    => 1,
            'name'  => 'Notebook',
            'price' => 3500.00,
            'stock' => 10,
        ];
        $this->assertSame($expected, $product->toArray());
    }
}
