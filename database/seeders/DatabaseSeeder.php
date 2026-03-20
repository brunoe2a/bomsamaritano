<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Chamada;
use App\Models\ChamadaAluno;
use App\Models\Curso;
use App\Models\Doador;
use App\Models\FinanceiroCategoria;
use App\Models\FinanceiroLancamento;
use App\Models\Matricula;
use App\Models\Professor;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Models\User;
use App\Models\Voluntario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles & Permissions
        $this->call(RolePermissionSeeder::class);

        // Admin user
        $admin = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@bomsamaritano.org',
        ]);
        $admin->assignRole('admin');

        // Coordenador
        $coordenador = User::factory()->create([
            'name' => 'Coordenador',
            'email' => 'coordenador@bomsamaritano.org',
        ]);
        $coordenador->assignRole('coordenador');

        // Professor
        $profUser = User::factory()->create([
            'name' => 'Professor',
            'email' => 'professor@bomsamaritano.org',
        ]);
        $profUser->assignRole('professor');

        // Financeiro
        $finUser = User::factory()->create([
            'name' => 'Financeiro',
            'email' => 'financeiro@bomsamaritano.org',
        ]);
        $finUser->assignRole('financeiro');

        // ============ CURSOS ============
        $cursos = collect([
            ['nome' => 'Português', 'descricao' => 'Reforço escolar de Língua Portuguesa', 'carga_horaria' => 80, 'periodo' => 'tarde'],
            ['nome' => 'Matemática', 'descricao' => 'Reforço escolar de Matemática', 'carga_horaria' => 80, 'periodo' => 'tarde'],
            ['nome' => 'Inglês', 'descricao' => 'Curso de Língua Inglesa básico e intermediário', 'carga_horaria' => 60, 'periodo' => 'tarde'],
            ['nome' => 'Violão', 'descricao' => 'Aulas de violão para iniciantes', 'carga_horaria' => 40, 'periodo' => 'manha'],
            ['nome' => 'Informática', 'descricao' => 'Informática básica: digitação, internet e ferramentas', 'carga_horaria' => 60, 'periodo' => 'manha'],
        ])->map(fn ($c) => Curso::create(array_merge($c, [
            'dias_semana' => ['1', '3', '5'],
            'max_alunos' => 25,
            'status' => 'ativo',
        ])));

        // ============ PROFESSORES ============
        $professoresData = [
            ['nome' => 'Maria Silva Santos', 'especialidade' => ['Português'], 'tipo_vinculo' => 'voluntario', 'email' => 'maria@email.com', 'telefone' => '(11) 99999-1111', 'whatsapp' => '(11) 99999-1111'],
            ['nome' => 'João Pedro Oliveira', 'especialidade' => ['Matemática'], 'tipo_vinculo' => 'contratado', 'email' => 'joao@email.com', 'telefone' => '(11) 99999-2222', 'whatsapp' => '(11) 99999-2222'],
            ['nome' => 'Ana Carolina Ferreira', 'especialidade' => ['Inglês'], 'tipo_vinculo' => 'voluntario', 'email' => 'ana@email.com', 'telefone' => '(11) 99999-3333', 'whatsapp' => '(11) 99999-3333'],
            ['nome' => 'Carlos Eduardo Lima', 'especialidade' => ['Violão'], 'tipo_vinculo' => 'voluntario', 'email' => 'carlos@email.com', 'telefone' => '(11) 99999-4444', 'whatsapp' => '(11) 99999-4444'],
            ['nome' => 'Fernanda Costa Souza', 'especialidade' => ['Informática'], 'tipo_vinculo' => 'contratado', 'email' => 'fernanda@email.com', 'telefone' => '(11) 99999-5555', 'whatsapp' => '(11) 99999-5555'],
        ];

        $professores = collect($professoresData)->map(function ($p) {
            $professor = Professor::create(array_merge($p, [
                'data_nascimento' => fake('pt_BR')->dateTimeBetween('-50 years', '-25 years'),
                'cpf' => fake('pt_BR')->unique()->numerify('###.###.###-##'),
                'data_inicio' => fake('pt_BR')->dateTimeBetween('-3 years', '-6 months'),
                'status' => 'ativo',
            ]));
            $professor->unidades()->attach(1); // Atribuir à Sede
            return $professor;
        });

        // ============ TURMAS ============
        $turmas = collect();
        foreach ($cursos as $i => $curso) {
            $turma = Turma::create([
                'nome' => "{$curso->nome} - Turma A",
                'curso_id' => $curso->id,
                'unidade_id' => 1, // Sede
                'horario_inicio' => '14:00',
                'horario_fim' => '16:00',
                'dias_semana' => ['1', '3', '5'],
                'periodo' => 'segunda_sexta',
                'capacidade_maxima' => 25,
                'ano_letivo' => (int) date('Y'),
                'status' => 'em_andamento',
            ]);
            $turma->professores()->attach($professores[$i]->id);
            $turmas->push($turma);
        }

        // ============ RESPONSÁVEIS + ALUNOS + MATRÍCULAS ============
        $nomes = [
            'Lucas Gabriel', 'Maria Eduarda', 'Pedro Henrique', 'Ana Júlia', 'Arthur Miguel',
            'Sophia Valentina', 'Davi Lucas', 'Isabella Cristina', 'Gabriel Santos', 'Laura Beatriz',
            'Enzo Daniel', 'Helena Rosa', 'Bernardo Alves', 'Valentina Souza', 'Matheus Oliveira',
            'Alice Fernandes', 'Rafael Costa', 'Manuela Lima', 'Théo Ribeiro', 'Heloísa Martins',
            'Samuel Pereira', 'Cecília Rocha', 'Nicolas Barbosa', 'Isadora Campos', 'Murilo Dias',
        ];

        $alunos = collect();
        foreach ($nomes as $nome) {
            $responsavel = Responsavel::create([
                'nome' => fake('pt_BR')->name(),
                'endereco_rua' => fake('pt_BR')->streetName(),
                'endereco_numero' => fake('pt_BR')->buildingNumber(),
                'endereco_bairro' => fake('pt_BR')->citySuffix() . ' ' . fake('pt_BR')->lastName(),
                'endereco_cidade' => fake('pt_BR')->city(),
                'endereco_estado' => fake('pt_BR')->stateAbbr(),
                'endereco_cep' => fake('pt_BR')->numerify('#####-###'),
                'telefone' => fake('pt_BR')->phoneNumber(),
                'whatsapp' => fake('pt_BR')->phoneNumber(),
                'cpf' => fake('pt_BR')->unique()->numerify('###.###.###-##'),
                'renda_familiar' => fake('pt_BR')->randomElement(['menos_1_salario', 'ate_2_salarios', 'acima_3_salarios']),
                'veiculo_proprio' => fake('pt_BR')->boolean(30),
                'casa_propria' => fake('pt_BR')->boolean(50),
                'cadastro_cras' => fake('pt_BR')->boolean(40),
                'auxilio_governo' => fake('pt_BR')->boolean(60),
                'desempregado' => fake('pt_BR')->boolean(25),
                'autorizacao_sozinho' => fake('pt_BR')->boolean(60),
                'autorizacao_imagem' => fake('pt_BR')->boolean(90),
            ]);

            $aluno = Aluno::create([
                'nome' => $nome,
                'data_nascimento' => fake('pt_BR')->dateTimeBetween('-16 years', '-7 years'),
                'ano_escolar' => fake('pt_BR')->randomElement(['1º Ano', '2º Ano', '3º Ano', '4º Ano', '5º Ano', '6º Ano', '7º Ano', '8º Ano', '9º Ano']),
                'responsavel_id' => $responsavel->id,
                'status' => 'ativo',
            ]);

            // Matricular em 1-3 turmas aleatórias
            $turmasSorteadas = $turmas->random(rand(1, 3));
            foreach ($turmasSorteadas as $turma) {
                Matricula::create([
                    'aluno_id' => $aluno->id,
                    'turma_id' => $turma->id,
                    'tipo' => 'nova',
                    'ano_letivo' => (int) date('Y'),
                    'status' => 'ativa',
                    'data_matricula' => fake('pt_BR')->dateTimeBetween('-3 months', 'now'),
                ]);
            }

            $alunos->push($aluno);
        }

        // ============ CHAMADAS (últimas 2 semanas) ============
        foreach ($turmas as $turma) {
            $professorTurmaId = \Illuminate\Support\Facades\DB::table('professor_turma')->where('turma_id', $turma->id)->value('professor_id');

            $alunosDaTurma = Matricula::where('turma_id', $turma->id)
                ->where('status', 'ativa')
                ->pluck('aluno_id');

            for ($d = 14; $d >= 1; $d--) {
                $data = Carbon::today()->subDays($d);
                if ($data->isWeekend()) continue;

                $chamada = Chamada::create([
                    'turma_id' => $turma->id,
                    'data' => $data,
                    'professor_id' => $professorTurmaId,
                ]);

                foreach ($alunosDaTurma as $alunoId) {
                    ChamadaAluno::create([
                        'chamada_id' => $chamada->id,
                        'aluno_id' => $alunoId,
                        'presente' => fake()->boolean(85),
                    ]);
                }
            }
        }

        // ============ VOLUNTÁRIOS ============
        $voluntariosData = [
            ['nome' => 'Roberto Azevedo', 'area_atuacao' => 'Apoio Pedagógico'],
            ['nome' => 'Patrícia Mendes', 'area_atuacao' => 'Administrativo'],
            ['nome' => 'Marcos Vinícius', 'area_atuacao' => 'TI'],
            ['nome' => 'Luciana Fonseca', 'area_atuacao' => 'Eventos'],
        ];

        foreach ($voluntariosData as $v) {
            $voluntario = Voluntario::create(array_merge($v, [
                'cpf' => fake('pt_BR')->unique()->numerify('###.###.###-##'),
                'telefone' => fake('pt_BR')->phoneNumber(),
                'whatsapp' => fake('pt_BR')->phoneNumber(),
                'email' => fake('pt_BR')->safeEmail(),
                'data_nascimento' => fake('pt_BR')->dateTimeBetween('-50 years', '-20 years'),
                'data_inicio' => fake('pt_BR')->dateTimeBetween('-2 years', '-1 month'),
                'status' => 'ativo',
            ]));
            $voluntario->unidades()->attach(1); // Atribuir à Sede
        }

        // ============ FINANCEIRO ============
        $categoriasReceita = [
            'Doações', 'Eventos', 'Patrocínios', 'Subvenções',
        ];
        $categoriasDespesa = [
            'Material Escolar', 'Manutenção', 'Alimentação', 'Salários', 'Contas', 'Eventos', 'Outros',
        ];

        $cats = collect();
        foreach ($categoriasReceita as $nome) {
            $cats->push(FinanceiroCategoria::create(['nome' => $nome, 'tipo' => 'receita']));
        }
        foreach ($categoriasDespesa as $nome) {
            $cats->push(FinanceiroCategoria::create(['nome' => $nome, 'tipo' => 'despesa']));
        }

        // Doadores
        $doadores = collect();
        for ($i = 0; $i < 5; $i++) {
            $doadores->push(Doador::create([
                'nome' => fake('pt_BR')->company(),
                'tipo' => fake('pt_BR')->randomElement(['pessoa_fisica', 'pessoa_juridica']),
                'telefone' => fake('pt_BR')->phoneNumber(),
                'email' => fake('pt_BR')->companyEmail(),
            ]));
        }

        // Lançamentos últimos 6 meses
        for ($m = 5; $m >= 0; $m--) {
            $mesRef = Carbon::now()->subMonths($m);

            // Entradas
            for ($i = 0; $i < rand(2, 5); $i++) {
                $catReceita = $cats->where('tipo', 'receita')->random();
                FinanceiroLancamento::create([
                    'tipo' => 'entrada',
                    'categoria_id' => $catReceita->id,
                    'unidade_id' => 1, // Sede
                    'doador_id' => fake()->boolean(60) ? $doadores->random()->id : null,
                    'descricao' => "Receita: {$catReceita->nome}",
                    'valor' => fake()->randomFloat(2, 500, 5000),
                    'data' => $mesRef->copy()->day(rand(1, 28)),
                    'user_id' => $admin->id,
                ]);
            }

            // Saídas
            for ($i = 0; $i < rand(3, 7); $i++) {
                $catDespesa = $cats->where('tipo', 'despesa')->random();
                FinanceiroLancamento::create([
                    'tipo' => 'saida',
                    'categoria_id' => $catDespesa->id,
                    'unidade_id' => 1, // Sede
                    'descricao' => "Despesa: {$catDespesa->nome}",
                    'valor' => fake()->randomFloat(2, 100, 3000),
                    'data' => $mesRef->copy()->day(rand(1, 28)),
                    'user_id' => $admin->id,
                ]);
            }
        }
    }
}
