<?php

declare(strict_types=1);

namespace PluginName\Tests\Integration\PostTypes;

use lucatume\WPBrowser\TestCase\WPTestCase;

class ProjectPostTypeTest extends WPTestCase
{
    public function testProjectPostTypeIsRegistered(): void
    {
        $this->assertTrue(post_type_exists('project'));
    }
    
    public function testProjectPostTypeIsPublic(): void
    {
        $postType = get_post_type_object('project');
        
        $this->assertTrue($postType->public);
    }
    
    public function testProjectPostTypeShowsInRest(): void
    {
        $postType = get_post_type_object('project');
        
        $this->assertTrue($postType->show_in_rest);
    }
    
    public function testProjectPostTypeHasCorrectLabels(): void
    {
        $postType = get_post_type_object('project');
        
        $this->assertSame('Projects', $postType->labels->name);
        $this->assertSame('Project', $postType->labels->singular_name);
    }
    
    public function testProjectPostTypeSupportsExpectedFeatures(): void
    {
        $supports = get_all_post_type_supports('project');
        
        $this->assertArrayHasKey('title', $supports);
        $this->assertArrayHasKey('editor', $supports);
        $this->assertArrayHasKey('thumbnail', $supports);
        $this->assertArrayHasKey('excerpt', $supports);
        $this->assertArrayHasKey('custom-fields', $supports);
        $this->assertArrayHasKey('revisions', $supports);
    }
    
    public function testCanCreateProjectPost(): void
    {
        $postId = wp_insert_post([
            'post_type' => 'project',
            'post_title' => 'Test Project',
            'post_status' => 'publish',
        ]);
        
        $this->assertIsInt($postId);
        $this->assertGreaterThan(0, $postId);
        
        $post = get_post($postId);
        $this->assertSame('project', $post->post_type);
        $this->assertSame('Test Project', $post->post_title);
    }
}
