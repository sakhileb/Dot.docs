<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $system = User::first();

        if (! $system) {
            return;
        }

        $templates = [
            [
                'name'        => 'Professional Resume',
                'category'    => 'resume',
                'description' => 'A clean, professional resume template with sections for experience, education, and skills.',
                'is_global'   => true,
                'content'     => '<h1>Your Name</h1><p><strong>Email:</strong> you@example.com &nbsp;|&nbsp; <strong>Phone:</strong> (555) 000-0000 &nbsp;|&nbsp; <strong>LinkedIn:</strong> linkedin.com/in/yourname</p><h2>Summary</h2><p>Experienced professional with a strong background in [your field]. Skilled in [key skills]. Seeking to leverage expertise in [target role].</p><h2>Experience</h2><h3>Job Title — Company Name</h3><p><em>Month Year – Present</em></p><ul><li>Led [initiative] resulting in [outcome]</li><li>Collaborated with cross-functional teams to deliver [project]</li><li>Improved [metric] by [percentage] through [method]</li></ul><h3>Previous Job Title — Previous Company</h3><p><em>Month Year – Month Year</em></p><ul><li>Achieved [result] by implementing [strategy]</li><li>Managed a team of [N] people across [function]</li></ul><h2>Education</h2><h3>Degree, Major — University Name</h3><p><em>Year</em></p><h2>Skills</h2><p>Skill 1 &nbsp;•&nbsp; Skill 2 &nbsp;•&nbsp; Skill 3 &nbsp;•&nbsp; Skill 4 &nbsp;•&nbsp; Skill 5</p>',
            ],
            [
                'name'        => 'Project Proposal',
                'category'    => 'proposal',
                'description' => 'A structured project proposal with executive summary, goals, timeline, and budget.',
                'is_global'   => true,
                'content'     => '<h1>Project Proposal: [Project Name]</h1><p><strong>Prepared by:</strong> [Your Name] &nbsp;|&nbsp; <strong>Date:</strong> [Date] &nbsp;|&nbsp; <strong>Version:</strong> 1.0</p><h2>Executive Summary</h2><p>This proposal outlines the plan for [project name]. The goal is to [primary objective] by [target date], resulting in [key benefit].</p><h2>Problem Statement</h2><p>Currently, [describe the problem or opportunity]. This causes [negative impact] for [affected parties].</p><h2>Proposed Solution</h2><p>We propose to [describe solution]. This approach will [explain how it solves the problem].</p><h2>Goals & Objectives</h2><ul><li>Goal 1: [Specific, measurable outcome]</li><li>Goal 2: [Specific, measurable outcome]</li><li>Goal 3: [Specific, measurable outcome]</li></ul><h2>Timeline</h2><table><thead><tr><th>Phase</th><th>Description</th><th>Start</th><th>End</th></tr></thead><tbody><tr><td>Phase 1</td><td>Discovery & Planning</td><td>[Date]</td><td>[Date]</td></tr><tr><td>Phase 2</td><td>Implementation</td><td>[Date]</td><td>[Date]</td></tr><tr><td>Phase 3</td><td>Testing & Launch</td><td>[Date]</td><td>[Date]</td></tr></tbody></table><h2>Budget</h2><ul><li>Personnel: $[amount]</li><li>Tools & Software: $[amount]</li><li>Contingency (10%): $[amount]</li><li><strong>Total: $[amount]</strong></li></ul><h2>Next Steps</h2><p>Upon approval, we will [first action] by [date].</p>',
            ],
            [
                'name'        => 'Meeting Notes',
                'category'    => 'notes',
                'description' => 'Capture meeting agenda, attendees, discussion points, and action items.',
                'is_global'   => true,
                'content'     => '<h1>Meeting Notes</h1><p><strong>Date:</strong> [Date] &nbsp;|&nbsp; <strong>Time:</strong> [Time] &nbsp;|&nbsp; <strong>Location:</strong> [Location / Video Call]</p><h2>Attendees</h2><ul><li>[Name] — [Role]</li><li>[Name] — [Role]</li><li>[Name] — [Role]</li></ul><h2>Agenda</h2><ol><li>[Agenda item 1]</li><li>[Agenda item 2]</li><li>[Agenda item 3]</li></ol><h2>Discussion</h2><h3>1. [Agenda Item 1]</h3><p>[Summary of discussion, decisions made, key points]</p><h3>2. [Agenda Item 2]</h3><p>[Summary of discussion, decisions made, key points]</p><h2>Action Items</h2><table><thead><tr><th>Action</th><th>Owner</th><th>Due Date</th></tr></thead><tbody><tr><td>[Action item 1]</td><td>[Name]</td><td>[Date]</td></tr><tr><td>[Action item 2]</td><td>[Name]</td><td>[Date]</td></tr></tbody></table><h2>Next Meeting</h2><p><strong>Date:</strong> [Date] &nbsp;|&nbsp; <strong>Agenda:</strong> [Preview of next topics]</p>',
            ],
            [
                'name'        => 'Blog Post',
                'category'    => 'blog',
                'description' => 'A full-length blog post template with introduction, body sections, and a compelling CTA.',
                'is_global'   => true,
                'content'     => '<h1>[Your Compelling Blog Title Here]</h1><p><em>By [Author Name] &nbsp;|&nbsp; [Date] &nbsp;|&nbsp; [Category]</em></p><h2>Introduction</h2><p>Hook your reader here. Start with a surprising statistic, a bold claim, or a relatable scenario. Then briefly explain what this post covers and why the reader should keep reading.</p><h2>[Section 1: Main Point]</h2><p>Dive into your first main point. Use clear, concise language. Support your claims with evidence, examples, or data.</p><blockquote>"An inspiring quote or key takeaway can go here to break up the text."</blockquote><h2>[Section 2: Main Point]</h2><p>Continue building your argument or narrative. Use subheadings, bullet points, or numbered lists to improve readability.</p><ul><li>Key point or tip #1</li><li>Key point or tip #2</li><li>Key point or tip #3</li></ul><h2>[Section 3: Main Point]</h2><p>Address common objections or alternative viewpoints. This adds credibility and depth to your writing.</p><h2>Conclusion</h2><p>Summarize your key points and reinforce the main message. Leave the reader with a clear takeaway.</p><p><strong>What do you think about [topic]? Share your thoughts in the comments below!</strong></p>',
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::firstOrCreate(
                ['name' => $template['name'], 'is_global' => true],
                array_merge($template, ['created_by' => $system->id])
            );
        }
    }
}
