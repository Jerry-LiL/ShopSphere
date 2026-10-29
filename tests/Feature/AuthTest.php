<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_customer_can_register_successfully(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => User::ROLE_CUSTOMER,
            'phone' => '+1-555-1234',
        ]);

        $response->assertRedirect(route('customer.orders.index'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_vendor_registration_creates_store_profile(): void
    {
        $response = $this->post('/register', [
            'name' => 'Carlos Mendez',
            'email' => 'carlos@vendor.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => User::ROLE_VENDOR,
            'store_name' => 'Sierra Crafted Gear',
            'store_description' => 'Artisanal outdoor tools and leathercraft.',
        ]);

        $response->assertRedirect(route('vendor.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'carlos@vendor.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isVendor());

        $this->assertDatabaseHas('stores', [
            'user_id' => $user->id,
            'name' => 'Sierra Crafted Gear',
        ]);
    }

    public function test_vendor_registration_fails_without_store_name(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Incomplete Vendor',
            'email' => 'incomplete@vendor.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => User::ROLE_VENDOR,
            'store_name' => '',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('store_name');
        $this->assertGuest();
    }

    public function test_customer_login_redirects_to_orders(): void
    {
        $user = User::create([
            'name' => 'Customer One',
            'email' => 'customer1@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->post('/login', [
            'email' => 'customer1@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.orders.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_vendor_login_redirects_to_vendor_dashboard(): void
    {
        $vendor = User::create([
            'name' => 'Merchant Mark',
            'email' => 'mark@vendor.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_VENDOR,
        ]);

        Store::create([
            'user_id' => $vendor->id,
            'name' => 'Mark Goods',
            'slug' => 'mark-goods',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'mark@vendor.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('vendor.dashboard'));
        $this->assertAuthenticatedAs($vendor);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@shopsphere.test',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@shopsphere.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_login_credentials_fail(): void
    {
        User::create([
            'name' => 'Customer Two',
            'email' => 'customer2@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'customer2@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_customer_cannot_access_vendor_or_admin_panels(): void
    {
        $customer = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($customer)->get('/vendor')->assertStatus(403);
        $this->actingAs($customer)->get('/admin')->assertStatus(403);
    }

    public function test_vendor_cannot_access_admin_panel(): void
    {
        $vendor = User::create([
            'name' => 'Store Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VENDOR,
        ]);

        $this->actingAs($vendor)->get('/admin')->assertStatus(403);
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::create([
            'name' => 'Logged User',
            'email' => 'logged@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect(route('catalog.index'));
        $this->assertGuest();
    }
}
