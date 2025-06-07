<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Electronics',
            'Home & Kitchen',
            'Clothing',
            'Books',
            'Sports & Outdoors',
            'Health & Beauty',
            'Automotive',
            'Tools & Hardware',
            'Toys & Games',
            'Garden & Outdoor'
        ];

        $productTypes = [
            'Electronics' => ['iPhone', 'Samsung Galaxy', 'MacBook', 'Dell Laptop', 'Gaming Mouse', 'Mechanical Keyboard', 'Bluetooth Headphones', 'Tablet', '4K Monitor', 'Webcam'],
            'Home & Kitchen' => ['Coffee Maker', 'Blender', 'Air Fryer', 'Microwave', 'Stand Mixer', 'Vacuum Cleaner', 'Rice Cooker', 'Toaster', 'Food Processor', 'Electric Kettle'],
            'Clothing' => ['T-Shirt', 'Jeans', 'Sneakers', 'Hoodie', 'Dress', 'Jacket', 'Sweater', 'Shorts', 'Boots', 'Cap'],
            'Books' => ['Programming Guide', 'Cookbook', 'Novel', 'Biography', 'Self-Help Book', 'History Book', 'Science Fiction', 'Romance Novel', 'Technical Manual', 'Art Book'],
            'Sports & Outdoors' => ['Running Shoes', 'Yoga Mat', 'Bicycle', 'Tent', 'Backpack', 'Water Bottle', 'Fitness Tracker', 'Dumbbells', 'Soccer Ball', 'Hiking Boots']
        ];

        $category = fake()->randomElement($categories);
        $productType = fake()->randomElement($productTypes[$category] ?? ['Generic Product']);
        $brand = fake()->company();

        return [
            'name' => $brand . ' ' . $productType,
            'sku' => strtoupper(fake()->bothify('???-####')),
            'description' => fake()->paragraphs(2, true),
            'category' => $category,
            'image_url' => fake()->imageUrl(400, 400, 'products'),
            'is_active' => fake()->boolean(90), // 90% active
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function electronics(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Electronics',
            'name' => fake()->randomElement(['Apple', 'Samsung', 'Sony', 'LG', 'Dell']) . ' ' .
                fake()->randomElement(['Smartphone', 'Laptop', 'Tablet', 'Monitor', 'Headphones']),
        ]);
    }
}
