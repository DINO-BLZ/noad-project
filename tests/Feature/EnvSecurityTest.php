<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvSecurityTest extends TestCase
{
    public function test_env_example_contains_no_default_admin_credentials(): void
    {
        $envExamplePath = base_path('.env.example');
        $this->assertFileExists($envExamplePath);

        $contents = file_get_contents($envExamplePath);

        $this->assertStringNotContainsString('ADMIN_PASSWORD="', $contents);
        $this->assertStringNotContainsString('ADMIN_PASSWORD=Admin', $contents);
        $this->assertStringNotContainsString('Admin2026!Noad', $contents);
        $this->assertStringNotContainsString('admin@noad.dz', $contents);

        $this->assertMatchesRegularExpression('/^ADMIN_NAME=\s*$/m', $contents);
        $this->assertMatchesRegularExpression('/^ADMIN_EMAIL=\s*$/m', $contents);
        $this->assertMatchesRegularExpression('/^ADMIN_PASSWORD=\s*$/m', $contents);
    }

    public function test_env_example_contains_no_sensitive_secrets(): void
    {
        $envExamplePath = base_path('.env.example');
        $contents = file_get_contents($envExamplePath);

        $this->assertMatchesRegularExpression('/^APP_KEY=\s*$/m', $contents);
        $this->assertMatchesRegularExpression('/^AWS_ACCESS_KEY_ID=\s*$/m', $contents);
        $this->assertMatchesRegularExpression('/^AWS_SECRET_ACCESS_KEY=\s*$/m', $contents);
    }
}
