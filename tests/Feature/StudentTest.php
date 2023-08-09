<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {

        // test index function
        $response = $this->get('/api/students');

        $response->assertStatus(200);
    }

    public function testStore()
    {
        // test store function
        $response = $this->post('/api/student/store', [
            'name' => 'Test Student1',
            'email' => 'test1@gmai.com',
            'phone' => '0123456789',
            'age' => '20'
        ]);

        $response->assertStatus(200);
    }

    public function testGetStudentById()
    {
        // test get student by id function
        $response = $this->get('/api/student/details/1');

        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        // check if student id 9 exists
        $response = $this->get('/api/student/details/9');
        $response->assertStatus(200);

        $updateName = 'Test Student2';
        $updateEmail = 'test2@gmail.com';
        $updatePhone = '0123456789';
        $updateAge = '20';

        // update student id 9
        $response = $this->put('/api/student/update/9', [
            'name' => $updateName,
            'email' => $updateEmail,
            'phone' => $updatePhone,
            'age' => $updateAge
        ]);

        $response->assertStatus(200);
        $response->assertJsonData($response, [
            'success' => true,
            'message' => 'Student data is successfully updated',
            'data' => [
                'name' => $updateName,
                'email' => $updateEmail,
                'phone' => $updatePhone,
                'age' => $updateAge
            ]
        ]);
    }
}