<?php

use App\Models\User;
use App\Models\School;
use App\Models\Supplier;

// Create a test user with a UUID tenant_id
$user = User::create([
    'firstName' => 'Test',
    'lastName' => 'User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'tenant_id' => 'cb456486-a30f-48d1-bb16-1b0fca17b9f9',
]);

// Create a test school with the same UUID
$school = School::create([
    'id' => 'cb456486-a30f-48d1-bb16-1b0fca17b9f9',
    'data' => json_encode(['name' => 'Test School']),
]);

// Test creating a supplier with the same tenant_id
$supplier = Supplier::create([
    'tenant_id' => 'cb456486-a30f-48d1-bb16-1b0fca17b9f9',
    'name' => 'Test Supplier',
    'contact' => '1234567890',
    'email' => 'supplier@example.com',
    'address' => '123 Test Street',
]);

echo "Supplier created successfully: " . $supplier->name . "\n";
