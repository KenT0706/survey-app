<?php

namespace Database\Seeders;

use App\Models\Survey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mayshowa Group of Companies — Employee Engagement Survey 2026 (trilingual).
 *
 * Survey-level fields (title/description — including the submission deadline)
 * are ALWAYS synced on every run via updateOrCreate, so editing them here and
 * redeploying is enough — no need to touch the live database by hand.
 *
 * Questions are rebuilt (delete + recreate, inside a transaction) whenever the
 * $questions array's content changes AT ALL — not just its count — detected
 * via a hash stored on the survey. So rewording a question, tweaking one
 * option, or adding/removing a question all correctly trigger a rebuild on
 * the next deploy.
 *
 * ⚠️ Once real responses exist, a rebuild deletes them along with the
 * questions (answers cascade off question_id). Fine during setup/testing —
 * stop editing this file's $questions once the survey actually goes live.
 */
class MayshowaEngagementSurveySeeder extends Seeder
{
    private const LIKERT_OPTIONS = [
        'Strongly Disagree / Sangat Tidak Setuju / 非常不同意',
        'Disagree / Tidak Setuju / 不同意',
        'Neutral / Neutral / 中立',
        'Agree / Setuju / 同意',
        'Strongly Agree / Sangat Setuju / 非常同意',
    ];

    private const YES_NO = ['Yes', 'No'];

    public function run(): void
    {
        $slug = 'mayshowa-employee-engagement-survey-2026';

        // Edit the deadline / intro copy here — it will always take effect on
        // the next deploy, regardless of whether questions need rebuilding.
        $title = "MAYSHOWA GROUP\nEMPLOYEE ENGAGEMENT SURVEY 2026";
        $description = '"Working Together" — We Want to Hear From You.'
            . "\n"
            . 'Your answers are reported in groupings, not individually. There are no right or wrong answers. '
            . 'It takes about 10 minutes. Please submit by 30 September 2026.';
        $closingNote = 'Thank you for your honest feedback — Working Together, we improve together.';

        $questions = [
            // ---------- PART 1: ABOUT YOU ----------
            [
                'section' => 'PART 1: ABOUT YOU',
                'type' => 'single_choice',
                'question_text' => 'Which location are you based in? / Di manakah lokasi anda bertugas? / 您在哪个地点工作？',
                'options' => [
                    'Subang/Shah Alam/Kepong',
                    'Other parts of Malaysia / Kawasan lain di Malaysia / 马来西亚其他地区',
                    'Outside Malaysia / Luar Malaysia / 马来西亚境外',
                ],
            ],
            [
                'section' => 'PART 1: ABOUT YOU',
                'type' => 'single_choice',
                'question_text' => 'Which area do you work in? / Apakah bidang kerja anda? / 您在哪个部门工作？',
                'options' => [
                    'Deputy Director & above (any department) — tick this box only',
                    'Production/Warehouse/Logistic/Demand Planning',
                    'Sales & Marketing/Business Development',
                    'Finance/DX/Purchasing & Costing',
                    'HR & Admin/Compliance/Corporate Communication',
                ],
            ],
            [
                'section' => 'PART 1: ABOUT YOU',
                'type' => 'single_choice',
                'question_text' => 'How long have you worked in Mayshowa? / Sudah berapa lama anda bekerja di Mayshowa? / 您在Mayshowa工作多久了？',
                'options' => [
                    'Less than 3 years / Kurang daripada 3 tahun / 少于3年',
                    '3 years and above / 3 tahun dan ke atas / 3年及以上',
                ],
            ],

            // ---------- PART 2: HOW YOU FEEL ----------
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => 'I am happy working at Mayshowa. / Saya gembira bekerja di Mayshowa. / 我在Mayshowa工作得很开心。', 'options' => self::LIKERT_OPTIONS],
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => 'I feel proud to tell people that I work at Mayshowa. / Saya berasa bangga memberitahu orang lain bahawa saya bekerja di Mayshowa. / 我很自豪地告诉别人我在Mayshowa工作。', 'options' => self::LIKERT_OPTIONS],
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => 'I clearly understand what is expected of me in my job. / Saya faham dengan jelas apa yang diharapkan daripada saya dalam kerja saya. / 我清楚了解工作对我的要求。', 'options' => self::LIKERT_OPTIONS],
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => 'I have the tools and equipment I need to do my job well. / Saya mempunyai alatan dan peralatan yang diperlukan untuk melakukan kerja saya dengan baik. / 我拥有做好工作所需的工具和设备。', 'options' => self::LIKERT_OPTIONS],
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => 'My supervisor treats me fairly. / Penyelia saya melayan saya dengan adil. / 我的主管公平对待我。', 'options' => self::LIKERT_OPTIONS],
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => 'I can speak to my supervisor when I have a problem. / Saya boleh berbincang dengan penyelia saya apabila menghadapi masalah. / 遇到问题时，我可以与主管沟通。', 'options' => self::LIKERT_OPTIONS],
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => 'My team helps each other — we truly "work together". / Pasukan saya saling membantu — kami benar-benar "bekerja bersama". / 我的团队互相帮助 — 我们真正做到"共同合作"。', 'options' => self::LIKERT_OPTIONS],
            ['section' => 'PART 2: HOW YOU FEEL ABOUT WORKING HERE / BAHAGIAN 2: PERASAAN ANDA BEKERJA DI SINI / 第二部分：您在这里工作的感受', 'type' => 'single_choice', 'question_text' => "I see myself still working at Mayshowa in 2 years' time. / Saya melihat diri saya masih bekerja di Mayshowa dalam masa 2 tahun lagi. / 我认为两年后我仍会在Mayshowa工作。", 'options' => self::LIKERT_OPTIONS],

            // ---------- PART 3: 6 CORE VALUES ----------
            ["section" => "PART 3: OUR 6 CORE VALUES — DO YOU SEE THEM IN ACTION? / BAHAGIAN 3: 6 NILAI TERAS KAMI — ADAKAH ANDA MELIHATNYA DIAMALKAN? / 第三部分：我们的六大核心价值 — 您看到它们的实践吗？\n\n1. STABILITY / KESTABILAN / 稳定 — We provide caring, growth and work-life balance. / Kami menyediakan keprihatinan, pertumbuhan dan keseimbangan kerja-kehidupan. / 我们提供关怀、成长与工作生活的平衡", 'type' => 'single_choice', 'question_text' => 'I feel my job at Mayshowa is secure and stable. / Saya rasa pekerjaan saya di Mayshowa amat stabil. / 我觉得我在Mayshowa的工作稳定、有保障。', 'options' => self::LIKERT_OPTIONS],
            ["section" => "PART 3: OUR 6 CORE VALUES — DO YOU SEE THEM IN ACTION? / BAHAGIAN 3: 6 NILAI TERAS KAMI — ADAKAH ANDA MELIHATNYA DIAMALKAN? / 第三部分：我们的六大核心价值 — 您看到它们的实践吗？\n\n1. STABILITY / KESTABILAN / 稳定 — We provide caring, growth and work-life balance. / Kami menyediakan keprihatinan, pertumbuhan dan keseimbangan kerja-kehidupan. / 我们提供关怀、成长与工作生活的平衡", 'type' => 'single_choice', 'question_text' => 'I am able to balance my work and my personal / family life. / Saya dapat mengimbangi kerja dengan kehidupan peribadi / keluarga saya. / 我能够平衡工作与个人／家庭生活。', 'options' => self::LIKERT_OPTIONS],
            ["section" => "PART 3: OUR 6 CORE VALUES — DO YOU SEE THEM IN ACTION? / BAHAGIAN 3: 6 NILAI TERAS KAMI — ADAKAH ANDA MELIHATNYA DIAMALKAN? / 第三部分：我们的六大核心价值 — 您看到它们的实践吗？\n\n1. STABILITY / KESTABILAN / 稳定 — We provide caring, growth and work-life balance. / Kami menyediakan keprihatinan, pertumbuhan dan keseimbangan kerja-kehidupan. / 我们提供关怀、成长与工作生活的平衡", 'type' => 'single_choice', 'question_text' => 'The Company cares about my well-being. / Syarikat mengambil berat tentang kesejahteraan saya. / 公司关心我的身心健康。', 'options' => self::LIKERT_OPTIONS],

            ['section' => '2. QUALITY / KUALITI / 品质 — We do not compromise product quality. / Kami tidak berkompromi terhadap kualiti produk. / 我们绝不妥协产品品质', 'type' => 'single_choice', 'question_text' => 'My department takes product / work quality seriously. / Jabatan saya mengambil serius kualiti produk / kerja. / 我的部门认真对待产品／工作品质。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '2. QUALITY / KUALITI / 品质 — We do not compromise product quality. / Kami tidak berkompromi terhadap kualiti produk. / 我们绝不妥协产品品质', 'type' => 'single_choice', 'question_text' => 'I am trained well enough to do quality work. / Saya dilatih secukupnya untuk menghasilkan kerja yang berkualiti. / 我获得足够的培训来做出高品质的工作。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '2. QUALITY / KUALITI / 品质 — We do not compromise product quality. / Kami tidak berkompromi terhadap kualiti produk. / 我们绝不妥协产品品质', 'type' => 'single_choice', 'question_text' => 'When quality problems are reported, action is taken. / Apabila masalah kualiti dilaporkan, tindakan akan diambil. / 品质问题被反映后，会有行动跟进。', 'options' => self::LIKERT_OPTIONS],

            ['section' => '3. ENVIRONMENTAL SUSTAINABILITY / KELESTARIAN ALAM SEKITAR / 环境永续 — We minimise our impact on the environment. / Kami meminimumkan kesan terhadap alam sekitar. / 我们尽量减少对环境的影响', 'type' => 'single_choice', 'question_text' => 'The Company takes care of the environment in its daily operations (waste handling, recycling, pollution control). / Syarikat menjaga alam sekitar dalam operasi hariannya (pengurusan sisa, kitar semula, kawalan pencemaran). / 公司在日常运营中爱护环境（废料处理、回收、污染控制）。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '3. ENVIRONMENTAL SUSTAINABILITY / KELESTARIAN ALAM SEKITAR / 环境永续 — We minimise our impact on the environment. / Kami meminimumkan kesan terhadap alam sekitar. / 我们尽量减少对环境的影响', 'type' => 'single_choice', 'question_text' => 'I know what I should do in my own job to protect the environment. / Saya tahu apa yang perlu saya lakukan dalam kerja saya untuk melindungi alam sekitar. / 我知道在自己的工作中应如何保护环境。', 'options' => self::LIKERT_OPTIONS],

            ['section' => '4. INTEGRITY / INTEGRITI / 诚信 — We uphold the highest level of conduct. / Kami menjunjung tahap tingkah laku yang tertinggi. / 我们秉持最高的行为标准', 'type' => 'single_choice', 'question_text' => 'People here are honest in the way they work. / Orang di sini jujur dalam cara mereka bekerja. / 这里的人以诚实的方式工作。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '4. INTEGRITY / INTEGRITI / 诚信 — We uphold the highest level of conduct. / Kami menjunjung tahap tingkah laku yang tertinggi. / 我们秉持最高的行为标准', 'type' => 'single_choice', 'question_text' => 'I feel safe to report wrongdoing without fear of punishment. / Saya berasa selamat untuk melaporkan salah laku tanpa takut dihukum. / 我可以放心举报不当行为，不必担心受罚。', 'options' => self::LIKERT_OPTIONS],

            ['section' => '5. PEOPLE / PEKERJA / 以人为本 — Our employees are our greatest asset. / Pekerja kami adalah aset kami yang paling berharga. / 员工是我们最宝贵的资产', 'type' => 'single_choice', 'question_text' => 'I am treated with respect at work. / Saya dilayan dengan hormat di tempat kerja. / 我在工作中受到尊重。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '5. PEOPLE / PEKERJA / 以人为本 — Our employees are our greatest asset. / Pekerja kami adalah aset kami yang paling berharga. / 员工是我们最宝贵的资产', 'type' => 'single_choice', 'question_text' => 'My good work is noticed and appreciated. / Kerja baik saya diperhatikan dan dihargai. / 我的良好表现获得注意和赏识。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '5. PEOPLE / PEKERJA / 以人为本 — Our employees are our greatest asset. / Pekerja kami adalah aset kami yang paling berharga. / 员工是我们最宝贵的资产', 'type' => 'single_choice', 'question_text' => 'I have opportunities to learn and develop my skills & knowledge at Mayshowa. / Saya mempunyai peluang untuk belajar dan mengembangkan kemahiran & pengetahuan saya di Mayshowa. / 我在Mayshowa有学习和提升技能与知识的机会。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '5. PEOPLE / PEKERJA / 以人为本 — Our employees are our greatest asset. / Pekerja kami adalah aset kami yang paling berharga. / 员工是我们最宝贵的资产', 'type' => 'single_choice', 'question_text' => 'I have career growth opportunity at Mayshowa. / Saya mempunyai peluang kemajuan kerjaya di Mayshowa. / 我在Mayshowa有职业发展的机会。', 'options' => self::LIKERT_OPTIONS],

            ['section' => '6. CORPORATE RESPONSIBILITY / TANGGUNGJAWAB KORPORAT / 企业责任 — We are a law-abiding and caring corporate citizen. / Kami warga korporat yang mematuhi undang-undang dan prihatin. / 我们是守法且有爱心的企业公民', 'type' => 'single_choice', 'question_text' => 'The Company follows the law and does things the right way. / Syarikat mematuhi undang-undang dan melakukan perkara dengan cara yang betul. / 公司遵守法律，以正确的方式做事。', 'options' => self::LIKERT_OPTIONS],
            ['section' => '6. CORPORATE RESPONSIBILITY / TANGGUNGJAWAB KORPORAT / 企业责任 — We are a law-abiding and caring corporate citizen. / Kami warga korporat yang mematuhi undang-undang dan prihatin. / 我们是守法且有爱心的企业公民', 'type' => 'single_choice', 'question_text' => 'Mayshowa cares about the community and society, not just profit. / Mayshowa mengambil berat tentang komuniti dan masyarakat, bukan hanya keuntungan. / Mayshowa关心社区与社会，而不只是利润。', 'options' => self::LIKERT_OPTIONS],

            // ---------- PART 4: YES / NO ----------
            ['section' => 'PART 4: YES / NO QUESTIONS — Please tick (✓) ONE answer for each question.', 'type' => 'single_choice', 'question_text' => 'Do you understand your available benefits such as annual leave, medical, insurance, etc? / Adakah anda memahami faedah yang disediakan untuk anda seperti cuti tahunan, perubatan, insurans dan sebagainya? / 您了解自己可享有的福利吗（如年假、医疗、保险等）？', 'options' => self::YES_NO],
            ['section' => 'PART 4: YES / NO QUESTIONS — Please tick (✓) ONE answer for each question.', 'type' => 'single_choice', 'question_text' => 'Do you feel physically safe at your workplace? / Adakah anda berasa selamat dari segi fizikal di tempat kerja anda? / 您在工作场所感到人身安全吗？', 'options' => self::YES_NO],
            ['section' => 'PART 4: YES / NO QUESTIONS — Please tick (✓) ONE answer for each question.', 'type' => 'single_choice', 'question_text' => 'Do you know how to report a complaint or problem (e.g. harassment, safety issue)? / Adakah anda tahu cara melaporkan aduan atau masalah (cth. gangguan, isu keselamatan)? / 您知道如何报告投诉或问题吗（例如骚扰、安全问题）？', 'options' => self::YES_NO],
            ['section' => 'PART 4: YES / NO QUESTIONS — Please tick (✓) ONE answer for each question.', 'type' => 'single_choice', 'question_text' => 'Would you recommend Mayshowa as a good place to work to your friends or family? / Adakah anda akan mengesyorkan Mayshowa sebagai tempat kerja yang baik kepada rakan atau keluarga anda? / 您会向亲友推荐Mayshowa是一个好的工作场所吗？', 'options' => self::YES_NO],

            // ---------- PART 5: IN YOUR OWN WORDS (open-ended) ----------
            ['section' => 'Part 5 — In Your Own Words', 'type' => 'textarea', 'question_text' => 'What is the ONE thing you like MOST about working at Mayshowa? / Apakah SATU perkara yang anda paling SUKA tentang bekerja di Mayshowa? / 您在Mayshowa工作最喜欢的一件事是什么？', 'required' => false],
            ['section' => 'Part 5 — In Your Own Words', 'type' => 'textarea', 'question_text' => 'What is the ONE thing you would like the Company to IMPROVE? / Apakah SATU perkara yang anda ingin Syarikat PERBAIKI? / 您最希望公司改进的一件事是什么？', 'required' => false],
            ['section' => 'Part 5 — In Your Own Words', 'type' => 'textarea', 'question_text' => 'What is the ONE main reason you STAY at Mayshowa? / Apakah SATU sebab utama anda terus KEKAL di Mayshowa? / 您留在Mayshowa的最主要原因是什么？', 'required' => false],
            ['section' => 'Part 5 — In Your Own Words', 'type' => 'textarea', 'question_text' => 'What is the ONE thing that might make you LEAVE Mayshowa? / Apakah SATU perkara yang mungkin menyebabkan anda MENINGGALKAN Mayshowa? / 什么事情可能会让您离开Mayshowa？', 'required' => false],
            ['section' => 'Part 5 — In Your Own Words', 'type' => 'textarea', 'question_text' => 'Any other comments or suggestions for Management? / Sebarang komen atau cadangan lain untuk Pihak Pengurusan? / 对管理层还有其他意见或建议吗？', 'required' => false],
        ];

        // Survey-level fields always sync, regardless of question state below.
        $survey = Survey::updateOrCreate(
            ['slug' => $slug],
            ['title' => $title, 'description' => $description, 'closing_note' => $closingNote, 'is_active' => true]
        );

        // Fingerprint the FULL question set (text, options, sections, order —
        // not just the count) so editing a single word or option triggers a
        // rebuild, the same way adding or removing a question does.
        $hash = md5(json_encode($questions));

        if ($survey->seed_hash === $hash) {
            return; // content is identical to what's already seeded — nothing to do
        }

        DB::transaction(function () use ($survey, $questions, $hash) {
            $survey->questions()->delete(); // cascades to options too

            foreach ($questions as $i => $q) {
                $question = $survey->questions()->create([
                    'type' => $q['type'],
                    'section' => $q['section'] ?? null,
                    'question_text' => $q['question_text'],
                    'is_required' => $q['required'] ?? true,
                    'order' => $i,
                ]);

                foreach ($q['options'] ?? [] as $j => $optionText) {
                    $question->options()->create(['option_text' => $optionText, 'order' => $j]);
                }
            }

            $survey->update(['seed_hash' => $hash]);
        });
    }
}