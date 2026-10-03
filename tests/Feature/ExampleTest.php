<?php

namespace Tests\Feature;

use App\Models\AirConditioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_storefront_filters_products_by_brand(): void
    {
        AirConditioner::create(['name' => 'Casper 9000', 'brand' => 'Casper', 'price' => 7490000]);
        AirConditioner::create(['name' => 'Daikin 12000', 'brand' => 'Daikin', 'price' => 11990000]);

        $response = $this->get('/?brand=Casper');

        $response->assertOk()
            ->assertSee('Casper 9000')
            ->assertDontSee('Daikin 12000');
    }

    public function test_storefront_only_shows_brands_that_have_products(): void
    {
        AirConditioner::create(['name' => 'Casper 9000', 'brand' => 'Casper', 'price' => 7490000]);
        AirConditioner::create(['name' => 'Panasonic 12000', 'brand' => 'Panasonic', 'price' => 11990000]);

        $this->get('/')
            ->assertOk()
            ->assertSee(route('shop.index', ['brand' => 'Casper']))
            ->assertSee(route('shop.index', ['brand' => 'Panasonic']))
            ->assertDontSee(route('shop.index', ['brand' => 'Daikin']))
            ->assertDontSee(route('shop.index', ['brand' => 'Funiki']));
    }

    public function test_product_gallery_images_are_available_on_the_product(): void
    {
        $airConditioner = AirConditioner::create([
            'name' => 'Casper 12000',
            'brand' => 'Casper',
            'price' => 12990000,
            'image' => 'products/cover.jpg',
        ]);

        $airConditioner->images()->createMany([
            ['image_path' => 'products/gallery-1.jpg', 'sort_order' => 0],
            ['image_path' => 'products/gallery-2.jpg', 'sort_order' => 1],
        ]);

        $this->assertSame('products/gallery-1.jpg', $airConditioner->primary_image_path);
        $this->assertCount(2, $airConditioner->images);
        $this->assertSame('products/gallery-1.jpg', $airConditioner->images->first()->image_path);
    }
}
