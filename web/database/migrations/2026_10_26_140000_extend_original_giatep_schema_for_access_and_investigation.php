<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite' || ! Schema::hasTable('usuario') || ! Schema::hasTable('caso')) {
            return;
        }

        Schema::table('solicitud_acceso', function (Blueprint $table): void {
            if (! Schema::hasColumn('solicitud_acceso', 'Cargo_Solicitante')) {
                $table->string('Cargo_Solicitante', 100)->nullable();
            }
            if (! Schema::hasColumn('solicitud_acceso', 'Telefono_Solicitante')) {
                $table->string('Telefono_Solicitante', 30)->nullable();
            }
            if (! Schema::hasColumn('solicitud_acceso', 'Contrasena_Hash')) {
                $table->string('Contrasena_Hash', 255)->nullable();
            }
            if (! Schema::hasColumn('solicitud_acceso', 'Id_Dependencia_Solicitada')) {
                $table->unsignedInteger('Id_Dependencia_Solicitada')->nullable()->index();
            }
        });

        Schema::table('investigacion', function (Blueprint $table): void {
            if (! Schema::hasColumn('investigacion', 'Folio')) {
                $table->string('Folio', 40)->nullable()->unique();
            }
            if (! Schema::hasColumn('investigacion', 'Estado')) {
                $table->string('Estado', 30)->default('En investigación');
            }
            if (! Schema::hasColumn('investigacion', 'Relato_Final')) {
                $table->mediumText('Relato_Final')->nullable();
            }
        });

        Schema::table('pregunta_declaracion', function (Blueprint $table): void {
            if (! Schema::hasColumn('pregunta_declaracion', 'Variable')) {
                $table->string('Variable', 120)->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite' || ! Schema::hasTable('usuario') || ! Schema::hasTable('caso')) {
            return;
        }

        Schema::table('investigacion', function (Blueprint $table): void {
            $columns = array_values(array_filter(['Folio', 'Estado', 'Relato_Final'], fn (string $column): bool => Schema::hasColumn('investigacion', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('pregunta_declaracion', function (Blueprint $table): void {
            if (Schema::hasColumn('pregunta_declaracion', 'Variable')) {
                $table->dropUnique(['Variable']);
                $table->dropColumn('Variable');
            }
        });

        Schema::table('solicitud_acceso', function (Blueprint $table): void {
            $columns = array_values(array_filter(['Cargo_Solicitante', 'Telefono_Solicitante', 'Contrasena_Hash', 'Id_Dependencia_Solicitada'], fn (string $column): bool => Schema::hasColumn('solicitud_acceso', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
