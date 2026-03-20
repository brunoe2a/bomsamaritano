export type Especialidade = {
    id: number;
    nome: string;
};

export type Responsavel = {
    id: number;
    nome: string;
    endereco_rua?: string;
    endereco_numero?: string;
    endereco_complemento?: string;
    endereco_bairro?: string;
    endereco_cidade?: string;
    endereco_estado?: string;
    endereco_cep?: string;
    telefone?: string;
    whatsapp?: string;
    cpf?: string;
    renda_familiar?: 'menos_1_salario' | 'ate_2_salarios' | 'acima_3_salarios';
    veiculo_proprio: boolean;
    casa_propria: boolean;
    cadastro_cras: boolean;
    auxilio_governo: boolean;
    desempregado: boolean;
    autorizacao_sozinho: boolean;
    autorizacao_imagem: boolean;
};

export type Aluno = {
    id: number;
    nome: string;
    data_nascimento: string;
    idade?: number;
    ano_escolar?: string;
    foto?: string;
    responsavel_id: number;
    status: 'ativo' | 'inativo' | 'trancado' | 'concluido';
    observacoes?: string;
    responsavel?: Responsavel;
    matriculas?: Matricula[];
    created_at: string;
    updated_at: string;
};

export type Curso = {
    id: number;
    nome: string;
    descricao?: string;
    carga_horaria?: number;
    dias_semana?: string[];
    periodo: 'manha' | 'tarde' | 'noite';
    max_alunos: number;
    status: 'ativo' | 'inativo';
    turmas_count?: number;
    turmas?: Turma[];
};

export type Professor = {
    id: number;
    nome: string;
    foto?: string;
    cpf?: string;
    rg?: string;
    data_nascimento?: string;
    telefone?: string;
    whatsapp?: string;
    email?: string;
    especialidade?: string[];
    especialidades?: Especialidade[];
    tipo_vinculo: 'voluntario' | 'contratado';
    data_inicio?: string;
    status: 'ativo' | 'inativo';
    unidades?: Unidade[];
};

export type Turma = {
    id: number;
    nome: string;
    curso_id: number;
    horario_inicio?: string;
    horario_fim?: string;
    dias_semana?: string[];
    periodo: 'segunda_sexta' | 'sabados';
    capacidade_maxima: number;
    ano_letivo: number;
    status: 'planejada' | 'em_andamento' | 'encerrada';
    curso?: Curso;
    professores?: Professor[];
    voluntarios?: Voluntario[];
    unidade_id: number;
    unidade?: Unidade;
    alunos_count?: number;
    matriculas?: Matricula[];
};

export type Matricula = {
    id: number;
    aluno_id: number;
    turma_id: number;
    tipo: 'nova' | 'rematricula';
    ano_letivo: number;
    status: 'ativa' | 'cancelada' | 'trancada' | 'concluida';
    data_matricula: string;
    aluno?: Aluno;
    turma?: Turma;
};

export type Chamada = {
    id: number;
    turma_id: number;
    data: string;
    professor_id?: number;
    observacoes?: string;
    presencas?: ChamadaAluno[];
};

export type ChamadaAluno = {
    id: number;
    chamada_id: number;
    aluno_id: number;
    presente: boolean;
    observacao?: string;
    aluno?: Aluno;
};

export type Voluntario = {
    id: number;
    nome: string;
    foto?: string;
    cpf?: string;
    rg?: string;
    data_nascimento?: string;
    telefone?: string;
    whatsapp?: string;
    email?: string;
    endereco_rua?: string;
    endereco_numero?: string;
    endereco_complemento?: string;
    endereco_bairro?: string;
    endereco_cidade?: string;
    endereco_estado?: string;
    endereco_cep?: string;
    area_atuacao?: string;
    habilidade?: string; // Coluna habilidade original (singular)
    habilidades?: Habilidade[]; // Novo relacionamento (plural)
    data_inicio?: string;
    status: 'ativo' | 'inativo';
    unidades?: Unidade[];
};

export type Habilidade = {
    id: number;
    nome: string;
};

export type DashboardStats = {
    totalAlunosAtivos: number;
    alunosPorCurso: { nome: string; total: number }[];
    turmasHoje: number;
    chamadasPendentes: number;
    novosCadastrosMes: number;
    saldo: {
        entradas: number;
        saidas: number;
        atual: number;
    };
};

export type FrequenciaMensal = {
    mes: string;
    percentual: number;
};

export type Aniversariante = {
    id: number;
    nome: string;
    data_nascimento: string;
    idade: number;
    foto?: string;
};

export type FinanceiroCategoria = {
    id: number;
    nome: string;
    tipo: 'receita' | 'despesa';
    descricao?: string;
};

export type FinanceiroLancamento = {
    id: number;
    tipo: 'entrada' | 'saida';
    categoria_id: number;
    categoria?: FinanceiroCategoria;
    doador_id?: number;
    doador?: Doador;
    descricao: string;
    valor: number;
    data: string;
    comprovante?: string;
    user_id: number;
    usuario?: { name: string };
    unidade_id: number;
    unidade?: Unidade;
    observacoes?: string;
    created_at: string;
    updated_at: string;
};

export type Unidade = {
    id: number;
    nome: string;
    endereco?: string;
    telefone?: string;
    email?: string;
    contato_responsavel?: string;
    created_at: string;
    updated_at: string;
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

export type Doador = {
    id: number;
    nome: string;
    tipo: 'pessoa_fisica' | 'pessoa_juridica';
    cpf_cnpj?: string;
    telefone?: string;
    email?: string;
    endereco?: string;
    created_at: string;
    updated_at: string;
};
