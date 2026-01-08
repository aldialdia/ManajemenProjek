<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $manager = User::create([
            'name' => 'John Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        $member1 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        $member2 = User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        $member3 = User::create([
            'name' => 'Sarah Wilson',
            'email' => 'sarah@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        // Create Clients
        $client1 = Client::create([
            'name' => 'PT Teknologi Maju',
            'email' => 'contact@teknologimaju.com',
            'phone' => '+62 21 1234567',
            'company' => 'Teknologi Maju',
            'address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
        ]);

        $client2 = Client::create([
            'name' => 'CV Digital Kreatif',
            'email' => 'info@digitalkreatif.id',
            'phone' => '+62 22 7654321',
            'company' => 'Digital Kreatif',
            'address' => 'Jl. Braga No. 45, Bandung',
        ]);

        $client3 = Client::create([
            'name' => 'Startup Indonesia',
            'email' => 'hello@startupindonesia.co',
            'phone' => '+62 31 9876543',
            'company' => 'Startup Indonesia',
            'address' => 'Jl. Tunjungan No. 78, Surabaya',
        ]);

        // Create Projects
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Redesign company website with modern UI/UX standards. Include responsive design and improved navigation.',
            'status' => 'active',
            'client_id' => $client1->id,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(60),
            'budget' => 50000000,
        ]);

        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Build native mobile app for iOS and Android platforms with real-time sync capabilities.',
            'status' => 'active',
            'client_id' => $client2->id,
            'start_date' => now()->subDays(15),
            'end_date' => now()->addDays(120),
            'budget' => 150000000,
        ]);

        $project3 = Project::create([
            'name' => 'E-Commerce Platform',
            'description' => 'Complete e-commerce solution with payment gateway integration and inventory management.',
            'status' => 'on_hold',
            'client_id' => $client3->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(180),
            'budget' => 200000000,
        ]);

        $project4 = Project::create([
            'name' => 'Marketing Campaign Q1',
            'description' => 'Plan and execute Q1 marketing campaigns including social media and email marketing.',
            'status' => 'completed',
            'client_id' => $client1->id,
            'start_date' => now()->subDays(90),
            'end_date' => now()->subDays(10),
            'budget' => 25000000,
        ]);

        // Assign users to projects
        $project1->users()->attach([$manager->id, $member1->id, $member2->id]);
        $project2->users()->attach([$manager->id, $member2->id, $member3->id]);
        $project3->users()->attach([$manager->id, $member1->id]);
        $project4->users()->attach([$manager->id, $member3->id]);

        // Create Tasks for Project 1
        Task::create([
            'title' => 'Design Homepage Mockup',
            'description' => 'Create wireframe and high-fidelity mockup for the homepage',
            'project_id' => $project1->id,
            'assigned_to' => $member1->id,
            'priority' => 'high',
            'status' => 'done',
            'due_date' => now()->subDays(10),
        ]);

        Task::create([
            'title' => 'Implement Navigation Component',
            'description' => 'Build responsive navigation with dropdown menus',
            'project_id' => $project1->id,
            'assigned_to' => $member2->id,
            'priority' => 'medium',
            'status' => 'in_progress',
            'due_date' => now()->addDays(5),
        ]);

        Task::create([
            'title' => 'Setup CI/CD Pipeline',
            'description' => 'Configure automated testing and deployment',
            'project_id' => $project1->id,
            'assigned_to' => $manager->id,
            'priority' => 'high',
            'status' => 'review',
            'due_date' => now()->addDays(3),
        ]);

        Task::create([
            'title' => 'Content Migration',
            'description' => 'Migrate existing content to new CMS',
            'project_id' => $project1->id,
            'assigned_to' => $member1->id,
            'priority' => 'low',
            'status' => 'todo',
            'due_date' => now()->addDays(20),
        ]);

        // Create Tasks for Project 2
        Task::create([
            'title' => 'User Authentication Flow',
            'description' => 'Implement login, register, and password reset',
            'project_id' => $project2->id,
            'assigned_to' => $member2->id,
            'priority' => 'urgent',
            'status' => 'in_progress',
            'due_date' => now()->addDays(7),
        ]);

        Task::create([
            'title' => 'Push Notification Setup',
            'description' => 'Configure Firebase for push notifications',
            'project_id' => $project2->id,
            'assigned_to' => $member3->id,
            'priority' => 'medium',
            'status' => 'todo',
            'due_date' => now()->addDays(14),
        ]);

        Task::create([
            'title' => 'API Integration',
            'description' => 'Connect mobile app to backend API',
            'project_id' => $project2->id,
            'assigned_to' => $member2->id,
            'priority' => 'high',
            'status' => 'review',
            'due_date' => now()->addDays(10),
        ]);

        // Create Tasks for Project 3
        Task::create([
            'title' => 'Database Schema Design',
            'description' => 'Design database structure for products and orders',
            'project_id' => $project3->id,
            'assigned_to' => $manager->id,
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addDays(14),
        ]);

        Task::create([
            'title' => 'Payment Gateway Research',
            'description' => 'Evaluate payment gateway options (Midtrans, Xendit, etc)',
            'project_id' => $project3->id,
            'assigned_to' => $member1->id,
            'priority' => 'medium',
            'status' => 'todo',
            'due_date' => now()->addDays(21),
        ]);

        // Create some comments
        $task = Task::first();
        if ($task) {
            Comment::create([
                'body' => 'Great progress on this task! The design looks amazing.',
                'user_id' => $manager->id,
                'task_id' => $task->id,
            ]);

            Comment::create([
                'body' => 'Thanks! I\'ll add the final touches tomorrow.',
                'user_id' => $member1->id,
                'task_id' => $task->id,
            ]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo Accounts:');
        $this->command->info('- Admin: admin@example.com / password');
        $this->command->info('- Manager: manager@example.com / password');
        $this->command->info('- Member: jane@example.com / password');
    }
}
