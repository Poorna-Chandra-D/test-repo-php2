<?php

declare(strict_types=1);

namespace Blog\Controllers;

use Blog\Exceptions\ApiException;
use Blog\Models\Post;
use Blog\Repositories\PostRepository;
use Blog\Services\Request;
use Blog\Services\Response;

/**
 * Controller for blog post operations
 */
class PostController
{
    private PostRepository $postRepository;
    private Request $request;
    
    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->postRepository = new PostRepository();
    }
    
    /**
     * Get all posts or search posts
     * 
     * @return void
     * @throws ApiException
     */
    public function index(): void
    {
        $search = $this->request->query('search');
        
        if ($search) {
            $posts = $this->postRepository->findBySearchTerm($search);
        } else {
            $posts = $this->postRepository->findAll();
        }
        
        Response::success($posts);
    }
    
    /**
     * Get a single post by ID
     * 
     * @param string $id Post ID
     * @return void
     * @throws ApiException
     */
    public function show(string $id): void
    {
        $postId = (int) $id;
        
        if ($postId <= 0) {
            throw new ApiException('Invalid post ID', 400);
        }
        
        $post = $this->postRepository->findById($postId);
        
        Response::success($post->toArray());
    }
    
    /**
     * Create a new post
     * 
     * @return void
     * @throws ApiException
     */
    public function store(): void
    {
        $data = $this->request->getJsonBody();
        
        $this->validatePostData($data);
        
        $post = new Post(
            null,
            $data['title'],
            $data['content']
        );
        
        $createdPost = $this->postRepository->create($post);
        
        Response::success($createdPost->toArray(), 201);
    }
    
    /**
     * Update an existing post
     * 
     * @param string $id Post ID
     * @return void
     * @throws ApiException
     */
    public function update(string $id): void
    {
        $postId = (int) $id;
        
        if ($postId <= 0) {
            throw new ApiException('Invalid post ID', 400);
        }
        
        $data = $this->request->getJsonBody();
        
        $this->validatePostData($data);
        
        $post = new Post(
            null,
            $data['title'],
            $data['content']
        );
        
        $updatedPost = $this->postRepository->update($postId, $post);
        
        Response::success($updatedPost->toArray());
    }
    
    /**
     * Delete a post
     * 
     * @param string $id Post ID
     * @return void
     * @throws ApiException
     */
    public function destroy(string $id): void
    {
        $postId = (int) $id;
        
        if ($postId <= 0) {
            throw new ApiException('Invalid post ID', 400);
        }
        
        $this->postRepository->delete($postId);
        
        Response::success(null, 204);
    }
    
    /**
     * Validate post data
     * 
     * @param array $data
     * @return void
     * @throws ApiException
     */
    private function validatePostData(array $data): void
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($data['title']) > 255) {
            $errors['title'] = 'Title cannot exceed 255 characters';
        }
        
        if (empty($data['content'])) {
            $errors['content'] = 'Content is required';
        }
        
        if (!empty($errors)) {
            throw new ApiException('Validation failed', 422, $errors);
        }
    }
}