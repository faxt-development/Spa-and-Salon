<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WelcomeEmailTemplateTest extends TestCase
{
    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Associate user with company via pivot table
        DB::table('company_user')->insert([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'is_primary' => true,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_initializes_welcome_template_fields_from_existing_template()
    {
        // Create a welcome template
        $template = EmailCampaign::create([
            'name' => 'Welcome Email Template',
            'type' => 'welcome',
            'subject' => 'Welcome to Faxtina - Your Account Details',
            'content' => '<p>Welcome email content</p>',
            'from_email' => 'noreply@faxt.com',
            'from_name' => 'Faxtina',
            'status' => 'active',
            'is_template' => true,
            'is_readonly' => true,
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/admin/email/welcome');

        $response->assertOk()
            ->assertSee('Welcome Email Template - Copy')
            ->assertSee('Welcome to Faxtina - Your Account Details')
            ->assertSee('<p>Welcome email content</p>');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_saves_new_welcome_template_as_separate_record()
    {
        // Create a welcome template
        EmailCampaign::create([
            'name' => 'Welcome Email Template',
            'type' => 'welcome',
            'subject' => 'Welcome to Faxtina - Your Account Details',
            'content' => '<p>Original template content</p>',
            'from_email' => 'noreply@faxt.com',
            'from_name' => 'Faxtina',
            'status' => 'active',
            'is_template' => true,
            'is_readonly' => true,
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->post('/admin/email/welcome', [
            'name' => 'Custom Welcome Email',
            'subject' => 'Custom Welcome Subject',
            'content' => '<p>Custom welcome content</p>',
            'from_email' => 'custom@faxt.com',
            'from_name' => 'Custom Faxtina',
        ]);

        $response->assertRedirect('/admin/email/welcome');

        // Verify original template is preserved
        $this->assertDatabaseHas('email_campaigns', [
            'name' => 'Welcome Email Template',
            'content' => '<p>Original template content</p>',
            'is_template' => true,
            'company_id' => $this->company->id,
        ]);

        // Verify new template is created
        $this->assertDatabaseHas('email_campaigns', [
            'name' => 'Custom Welcome Email',
            'content' => '<p>Custom welcome content</p>',
            'is_template' => false,
            'company_id' => $this->company->id,
        ]);

        // Verify we have 2 records
        $this->assertCount(2, EmailCampaign::where('type', 'welcome')->get());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_scopes_templates_to_current_user_company()
    {
        // Create another company and user
        $otherCompany = Company::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->companies()->attach($otherCompany->id, [
            'is_primary' => true,
            'role' => 'admin'
        ]);

        // Create template for other company
        EmailCampaign::create([
            'name' => 'Other Company Welcome',
            'type' => 'welcome',
            'subject' => 'Welcome to Other Company',
            'content' => '<p>Other company content</p>',
            'from_email' => 'other@faxt.com',
            'from_name' => 'Other Company',
            'status' => 'active',
            'is_template' => false,
            'company_id' => $otherCompany->id,
            'user_id' => $otherUser->id,
        ]);

        // Create template for current company
        EmailCampaign::create([
            'name' => 'Current Company Welcome',
            'type' => 'welcome',
            'subject' => 'Welcome to Current Company',
            'content' => '<p>Current company content</p>',
            'from_email' => 'current@faxt.com',
            'from_name' => 'Current Company',
            'status' => 'active',
            'is_template' => false,
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/admin/email/welcome');

        $response->assertOk()
            ->assertSee('Current Company Welcome')
            ->assertDontSee('Other Company Welcome');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_assigns_company_id_to_new_templates()
    {
        $response = $this->post('/admin/email/welcome', [
            'name' => 'New Welcome Template',
            'subject' => 'New Welcome Subject',
            'content' => '<p>New welcome content</p>',
            'from_email' => 'new@faxt.com',
            'from_name' => 'New Faxtina',
        ]);

        $response->assertRedirect('/admin/email/welcome');

        $this->assertDatabaseHas('email_campaigns', [
            'name' => 'New Welcome Template',
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'is_template' => false,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_falls_back_to_global_template_when_no_company_template_exists()
    {
        // Create global template with no company_id
        EmailCampaign::create([
            'name' => 'Global Welcome Template',
            'type' => 'welcome',
            'subject' => 'Global Welcome Subject',
            'content' => '<p>Global welcome content</p>',
            'from_email' => 'global@faxt.com',
            'from_name' => 'Global Faxtina',
            'status' => 'active',
            'is_template' => true,
            'is_readonly' => true,
            'company_id' => null,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/admin/email/welcome');

        $response->assertOk()
            ->assertSee('Global Welcome Template - Copy')
            ->assertSee('Global Welcome Subject');
    }
}
