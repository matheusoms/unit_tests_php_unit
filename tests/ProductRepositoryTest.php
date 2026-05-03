<?php

declare(strict_types=1);

namespace Tests;

use App\Product;
use App\ProductRepository;
use PHPUnit\Framework\TestCase;

/**
 * ProductRepositoryTest — Testes de componente para ProductRepository
 *
 * Quadrante Q1 (Technology-Facing / Guide Development):
 *   - Testes de componente que verificam o comportamento de um grupo de
 *     classes que trabalham juntas (Product + ProductRepository)
 *   - Cobrem o ciclo completo de CRUD em memória
 */
class ProductRepositoryTest extends TestCase
{
    private ProductRepository $repo;

    protected function setUp(): void
    {
        // Cada teste recebe um repositório limpo — isolamento total
        $this->repo = new ProductRepository();
    }

    // ════════════════════════════════════════════════════════════════════════
    // CREATE
    // ════════════════════════════════════════════════════════════════════════

    public function testCreateReturnsProductWithAutoIncrementId(): void
    {
        $p1 = $this->repo->create('Product A', 10.0, 5);
        $p2 = $this->repo->create('Product B', 20.0, 3);

        $this->assertSame(1, $p1->getId());
        $this->assertSame(2, $p2->getId());
    }

    public function testCreateIncreasesCount(): void
    {
        $this->assertSame(0, $this->repo->count());
        $this->repo->create('Item', 1.0, 1);
        $this->assertSame(1, $this->repo->count());
    }

    public function testCreateThrowsForInvalidProduct(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->repo->create('', 10.0, 1); // nome vazio → inválido
    }

    // ════════════════════════════════════════════════════════════════════════
    // READ — findById
    // ════════════════════════════════════════════════════════════════════════

    public function testFindByIdReturnsCorrectProduct(): void
    {
        $created = $this->repo->create('Notebook', 3500.0, 10);
        $found   = $this->repo->findById($created->getId());

        $this->assertSame($created->getId(), $found->getId());
        $this->assertSame('Notebook', $found->getName());
    }

    public function testFindByIdThrowsWhenNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product with ID 99 not found.');
        $this->repo->findById(99);
    }

    // ════════════════════════════════════════════════════════════════════════
    // READ — findAll
    // ════════════════════════════════════════════════════════════════════════

    public function testFindAllReturnsEmptyArrayInitially(): void
    {
        $this->assertSame([], $this->repo->findAll());
    }

    public function testFindAllReturnsAllProducts(): void
    {
        $this->repo->create('A', 1.0, 1);
        $this->repo->create('B', 2.0, 2);
        $this->repo->create('C', 3.0, 3);

        $this->assertCount(3, $this->repo->findAll());
    }

    // ════════════════════════════════════════════════════════════════════════
    // READ — findAvailable
    // ════════════════════════════════════════════════════════════════════════

    public function testFindAvailableReturnsOnlyInStockProducts(): void
    {
        $this->repo->create('In Stock',     10.0, 5);
        $this->repo->create('Out of Stock', 20.0, 0);
        $this->repo->create('Also In Stock',30.0, 1);

        $available = $this->repo->findAvailable();

        $this->assertCount(2, $available);
        foreach ($available as $product) {
            $this->assertTrue($product->isAvailable());
        }
    }

    public function testFindAvailableReturnsEmptyWhenAllOutOfStock(): void
    {
        $this->repo->create('Out 1', 10.0, 0);
        $this->repo->create('Out 2', 20.0, 0);

        $this->assertSame([], $this->repo->findAvailable());
    }

    // ════════════════════════════════════════════════════════════════════════
    // UPDATE
    // ════════════════════════════════════════════════════════════════════════

    public function testUpdateChangesProductData(): void
    {
        $product = $this->repo->create('Old Name', 50.0, 3);
        $updated = $this->repo->update($product->getId(), 'New Name', 99.0, 10);

        $this->assertSame('New Name', $updated->getName());
        $this->assertSame(99.0, $updated->getPrice());
        $this->assertSame(10, $updated->getStock());
    }

    public function testUpdateReturnsSameInstance(): void
    {
        $product = $this->repo->create('Item', 10.0, 1);
        $updated = $this->repo->update($product->getId(), 'Item Updated', 20.0, 5);

        // Mesmo objeto em memória (repositório em memória mantém referência)
        $this->assertSame($product->getId(), $updated->getId());
    }

    public function testUpdateThrowsWhenProductNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->repo->update(999, 'Name', 1.0, 1);
    }

    public function testUpdateThrowsForInvalidData(): void
    {
        $product = $this->repo->create('Item', 10.0, 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->repo->update($product->getId(), '', 10.0, 1); // nome vazio
    }

    // ════════════════════════════════════════════════════════════════════════
    // DELETE
    // ════════════════════════════════════════════════════════════════════════

    public function testDeleteRemovesProduct(): void
    {
        $product = $this->repo->create('To Delete', 10.0, 1);
        $this->assertSame(1, $this->repo->count());

        $this->repo->delete($product->getId());

        $this->assertSame(0, $this->repo->count());
    }

    public function testDeleteThrowsWhenProductNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->repo->delete(999);
    }

    public function testDeletedProductCannotBeFound(): void
    {
        $product = $this->repo->create('Item', 10.0, 1);
        $id = $product->getId();
        $this->repo->delete($id);

        $this->expectException(\RuntimeException::class);
        $this->repo->findById($id);
    }

    // ════════════════════════════════════════════════════════════════════════
    // Cenários integrados
    // ════════════════════════════════════════════════════════════════════════

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

    public function testSellReducesStockAndAffectsAvailability(): void
    {
        $product = $this->repo->create('Limited', 50.0, 2);

        $product->sell(1);
        $this->assertTrue($product->isAvailable());
        $this->assertCount(1, $this->repo->findAvailable());

        $product->sell(1);
        $this->assertFalse($product->isAvailable());
        $this->assertCount(0, $this->repo->findAvailable());
    }
}
