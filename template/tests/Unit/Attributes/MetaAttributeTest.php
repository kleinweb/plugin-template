<?php

declare(strict_types=1);

namespace PluginName\Tests\Unit\Attributes;

use PluginName\Attributes\Meta;
use PluginName\Tests\Unit\TestCase;

final class MetaAttributeTest extends TestCase
{
    public function testCreatesMetaWithDefaults(): void
    {
        $meta = new Meta(key: 'test_key');
        
        $this->assertSame('test_key', $meta->key);
        $this->assertSame('post', $meta->objectType);
        $this->assertNull($meta->objectSubtype);
        $this->assertSame('string', $meta->type);
        $this->assertTrue($meta->single);
        $this->assertTrue($meta->showInRest);
        $this->assertTrue($meta->showInEditor);
    }
    
    public function testCreatesMetaWithCustomOptions(): void
    {
        $meta = new Meta(
            key: 'project_status',
            objectType: 'post',
            objectSubtype: 'project',
            type: 'string',
            label: 'Status',
            description: 'Project status',
            default: 'draft',
            single: true,
            showInRest: true,
            showInEditor: true,
            inputType: 'select',
            options: ['draft' => 'Draft', 'published' => 'Published']
        );
        
        $this->assertSame('project_status', $meta->key);
        $this->assertSame('project', $meta->objectSubtype);
        $this->assertSame('Status', $meta->label);
        $this->assertSame('draft', $meta->default);
        $this->assertSame('select', $meta->inputType);
        $this->assertSame(['draft' => 'Draft', 'published' => 'Published'], $meta->options);
    }
    
    public function testToArgsReturnsValidWordPressArgs(): void
    {
        $meta = new Meta(
            key: 'test_key',
            objectSubtype: 'page',
            type: 'string',
            description: 'Test description',
            default: 'default_value'
        );
        
        $args = $meta->toArgs();
        
        $this->assertIsArray($args);
        $this->assertArrayHasKey('type', $args);
        $this->assertArrayHasKey('description', $args);
        $this->assertArrayHasKey('single', $args);
        $this->assertArrayHasKey('default', $args);
        $this->assertArrayHasKey('show_in_rest', $args);
        $this->assertArrayHasKey('object_subtype', $args);
        
        $this->assertSame('string', $args['type']);
        $this->assertSame('Test description', $args['description']);
        $this->assertTrue($args['single']);
        $this->assertSame('default_value', $args['default']);
        $this->assertSame('page', $args['object_subtype']);
    }
    
    public function testShowInRestIncludesSchema(): void
    {
        $meta = new Meta(
            key: 'test_key',
            type: 'integer',
            description: 'A number',
            default: 42
        );
        
        $args = $meta->toArgs();
        
        $this->assertIsArray($args['show_in_rest']);
        $this->assertArrayHasKey('schema', $args['show_in_rest']);
        $this->assertSame('integer', $args['show_in_rest']['schema']['type']);
        $this->assertSame('A number', $args['show_in_rest']['schema']['description']);
        $this->assertSame(42, $args['show_in_rest']['schema']['default']);
    }
    
    public function testShowInRestFalseWhenDisabled(): void
    {
        $meta = new Meta(
            key: 'private_key',
            showInRest: false
        );
        
        $args = $meta->toArgs();
        
        $this->assertFalse($args['show_in_rest']);
    }
    
    public function testGetUiConfigReturnsExpectedShape(): void
    {
        $meta = new Meta(
            key: 'project_status',
            type: 'string',
            label: 'Status',
            description: 'Current status',
            default: 'active',
            inputType: 'select',
            options: ['active' => 'Active', 'inactive' => 'Inactive']
        );
        
        $config = $meta->getUiConfig();
        
        $this->assertSame('project_status', $config['key']);
        $this->assertSame('Status', $config['label']);
        $this->assertSame('Current status', $config['description']);
        $this->assertSame('string', $config['type']);
        $this->assertSame('select', $config['inputType']);
        $this->assertSame(['active' => 'Active', 'inactive' => 'Inactive'], $config['options']);
        $this->assertSame('active', $config['default']);
    }
    
    public function testInfersInputTypeFromDataType(): void
    {
        $stringMeta = new Meta(key: 'string_field', type: 'string');
        $this->assertSame('text', $stringMeta->getUiConfig()['inputType']);
        
        $intMeta = new Meta(key: 'int_field', type: 'integer');
        $this->assertSame('number', $intMeta->getUiConfig()['inputType']);
        
        $boolMeta = new Meta(key: 'bool_field', type: 'boolean');
        $this->assertSame('checkbox', $boolMeta->getUiConfig()['inputType']);
    }
    
    public function testInfersSelectForOptionsField(): void
    {
        $meta = new Meta(
            key: 'choice_field',
            type: 'string',
            options: ['a' => 'A', 'b' => 'B']
        );
        
        $this->assertSame('select', $meta->getUiConfig()['inputType']);
    }
    
    public function testDefaultValuesForEachType(): void
    {
        $types = [
            'string' => '',
            'integer' => 0,
            'number' => 0,
            'boolean' => false,
            'array' => [],
        ];
        
        foreach ($types as $type => $expectedDefault) {
            $meta = new Meta(key: 'test', type: $type);
            $args = $meta->toArgs();
            $this->assertSame($expectedDefault, $args['default'], "Failed for type: $type");
        }
    }
    
    public function testTermMetaConfiguration(): void
    {
        $meta = new Meta(
            key: 'category_color',
            objectType: 'term',
            objectSubtype: 'category',
            type: 'string',
            label: 'Color',
            inputType: 'color'
        );
        
        $this->assertSame('term', $meta->objectType);
        $this->assertSame('category', $meta->objectSubtype);
        $this->assertSame('color', $meta->inputType);
    }
    
    public function testUserMetaConfiguration(): void
    {
        $meta = new Meta(
            key: 'user_department',
            objectType: 'user',
            type: 'string',
            label: 'Department'
        );
        
        $this->assertSame('user', $meta->objectType);
        $this->assertNull($meta->objectSubtype);
    }
}
