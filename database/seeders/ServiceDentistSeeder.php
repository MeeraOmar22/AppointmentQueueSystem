<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Dentist;

class ServiceDentistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample services
        Service::firstOrCreate(
            ['name' => 'General Checkup'],
            [
                'description' => 'Regular dental checkup and cleaning with expert diagnosis and treatment recommendations.',
                'price' => 50.00,
                'estimated_duration' => 30,
                'status' => 1
            ]
        );

        Service::firstOrCreate(
            ['name' => 'Teeth Cleaning'],
            [
                'description' => 'Professional teeth cleaning to remove plaque and tartar buildup for a healthier smile.',
                'price' => 75.00,
                'estimated_duration' => 45,
                'status' => 1
            ]
        );

        Service::firstOrCreate(
            ['name' => 'Dental Filling'],
            [
                'description' => 'Restoration of decayed teeth using tooth-colored composite resin materials.',
                'price' => 120.00,
                'estimated_duration' => 60,
                'status' => 1
            ]
        );

        Service::firstOrCreate(
            ['name' => 'Root Canal Treatment'],
            [
                'description' => 'Specialized endodontic treatment to save infected or damaged teeth and relieve pain.',
                'price' => 300.00,
                'estimated_duration' => 90,
                'status' => 1
            ]
        );

        Service::firstOrCreate(
            ['name' => 'Teeth Whitening'],
            [
                'description' => 'Professional teeth whitening to brighten your smile and remove stains effectively.',
                'price' => 150.00,
                'estimated_duration' => 60,
                'status' => 1
            ]
        );

        Service::firstOrCreate(
            ['name' => 'Dental Implants'],
            [
                'description' => 'Advanced implant solutions for missing teeth restoration with natural appearance and functionality.',
                'price' => 800.00,
                'estimated_duration' => 120,
                'status' => 1
            ]
        );

        // Create sample dentists
        Dentist::firstOrCreate(
            ['email' => 'dr.john@clinic.com'],
            [
                'name' => 'Dr. John Smith',
                'specialization' => 'Implant Surgeon',
                'phone' => '06-677 1940',
                'status' => 1
            ]
        );

        Dentist::firstOrCreate(
            ['email' => 'dr.mary@clinic.com'],
            [
                'name' => 'Dr. Mary Johnson',
                'specialization' => 'Orthodontist',
                'phone' => '06-677 1940',
                'status' => 1
            ]
        );

        Dentist::firstOrCreate(
            ['email' => 'dr.robert@clinic.com'],
            [
                'name' => 'Dr. Robert Williams',
                'specialization' => 'Cosmetic Dentist',
                'phone' => '06-677 1940',
                'status' => 1
            ]
        );

        Dentist::firstOrCreate(
            ['email' => 'dr.sarah@clinic.com'],
            [
                'name' => 'Dr. Sarah Davis',
                'specialization' => 'Periodontist',
                'phone' => '06-677 1940',
                'status' => 1
            ]
        );

        Dentist::firstOrCreate(
            ['email' => 'dr.michael@clinic.com'],
            [
                'name' => 'Dr. Michael Brown',
                'specialization' => 'General Dentist',
                'phone' => '06-677 1940',
                'status' => 1
            ]
        );
    }
}
