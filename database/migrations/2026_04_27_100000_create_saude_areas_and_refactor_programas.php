<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_areas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->timestamps();
        });

        // Seed inicial das áreas conhecidas (mapeadas dos enums anteriores)
        $now = now();
        $areasIniciais = [
            'odontologia' => 'Odontologia',
            'psicologia' => 'Psicologia',
            'medica' => 'Médica',
            'nutricao' => 'Nutrição',
            'fonoaudiologia' => 'Fonoaudiologia',
            'geral' => 'Geral',
        ];
        $idsPorSlug = [];
        foreach ($areasIniciais as $slug => $nome) {
            $idsPorSlug[$slug] = DB::table('saude_areas')->insertGetId([
                'nome' => $nome,
                'status' => 'ativo',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Adiciona area_id (nullable durante a migração de dados)
        Schema::table('saude_programas', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('nome')->constrained('saude_areas')->cascadeOnDelete();
        });

        // Migra dados do enum antigo para FK
        foreach ($idsPorSlug as $slug => $id) {
            DB::table('saude_programas')->where('area', $slug)->update(['area_id' => $id]);
        }

        // Define um fallback (Geral) caso haja registros sem match
        DB::table('saude_programas')->whereNull('area_id')->update(['area_id' => $idsPorSlug['geral']]);

        // Drop coluna enum antiga
        Schema::table('saude_programas', function (Blueprint $table) {
            $table->dropIndex(['area', 'status']);
        });
        Schema::table('saude_programas', function (Blueprint $table) {
            $table->dropColumn('area');
        });

        // Torna area_id NOT NULL e adiciona índice
        Schema::table('saude_programas', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable(false)->change();
            $table->index(['area_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('saude_programas', function (Blueprint $table) {
            $table->enum('area', ['odontologia', 'psicologia', 'medica', 'nutricao', 'fonoaudiologia', 'geral'])->default('geral')->after('nome');
        });

        // Mapear de volta o nome para slug, melhor esforço
        $map = [
            'Odontologia' => 'odontologia',
            'Psicologia' => 'psicologia',
            'Médica' => 'medica',
            'Nutrição' => 'nutricao',
            'Fonoaudiologia' => 'fonoaudiologia',
            'Geral' => 'geral',
        ];
        foreach ($map as $nome => $slug) {
            $areaId = DB::table('saude_areas')->where('nome', $nome)->value('id');
            if ($areaId) {
                DB::table('saude_programas')->where('area_id', $areaId)->update(['area' => $slug]);
            }
        }

        Schema::table('saude_programas', function (Blueprint $table) {
            $table->dropIndex(['area_id', 'status']);
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
            $table->index(['area', 'status']);
        });

        Schema::dropIfExists('saude_areas');
    }
};
