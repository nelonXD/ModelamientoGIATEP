<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite' || ! Schema::hasTable('usuario') || ! Schema::hasTable('caso')) {
            return;
        }

        Schema::table('usuario', function (Blueprint $table): void {
            if (! Schema::hasColumn('usuario', 'Nombre')) {
                $table->string('Nombre', 100)->default('');
            }
            if (! Schema::hasColumn('usuario', 'Apellido')) {
                $table->string('Apellido', 100)->default('');
            }
            if (! Schema::hasColumn('usuario', 'Cargo')) {
                $table->string('Cargo', 100)->nullable();
            }
            if (! Schema::hasColumn('usuario', 'Telefono')) {
                $table->string('Telefono', 30)->nullable();
            }
            if (! Schema::hasColumn('usuario', 'Estado')) {
                $table->string('Estado', 20)->default('active')->index();
            }
        });

        Schema::table('rol', function (Blueprint $table): void {
            if (! Schema::hasColumn('rol', 'Codigo_Rol')) {
                $table->string('Codigo_Rol', 60)->nullable()->unique();
            }
        });

        Schema::table('empresa_empleador', function (Blueprint $table): void {
            if (! Schema::hasColumn('empresa_empleador', 'RUT_Empresa')) {
                $table->string('RUT_Empresa', 20)->nullable()->index();
            }
            if (! Schema::hasColumn('empresa_empleador', 'Actividad_Economica')) {
                $table->string('Actividad_Economica', 255)->nullable();
            }
        });

        Schema::table('caso', function (Blueprint $table): void {
            if (! Schema::hasColumn('caso', 'Folio')) {
                $table->string('Folio', 32)->nullable()->unique();
            }
        });

        Schema::table('trabajador_accidentado_enfermo', function (Blueprint $table): void {
            if (! Schema::hasColumn('trabajador_accidentado_enfermo', 'Tipo_Contrato')) {
                $table->string('Tipo_Contrato', 80)->nullable();
            }
            if (! Schema::hasColumn('trabajador_accidentado_enfermo', 'Tipo_Ingreso')) {
                $table->string('Tipo_Ingreso', 80)->nullable();
            }
            if (! Schema::hasColumn('trabajador_accidentado_enfermo', 'Jornada_Laboral')) {
                $table->string('Jornada_Laboral', 80)->nullable();
            }
        });

        Schema::table('detalle_accidente', function (Blueprint $table): void {
            if (! Schema::hasColumn('detalle_accidente', 'Tipo_Trayecto')) {
                $table->string('Tipo_Trayecto', 100)->nullable();
            }
        });

        Schema::table('evidencia', function (Blueprint $table): void {
            if (! Schema::hasColumn('evidencia', 'Fecha_Carga')) {
                $table->dateTime('Fecha_Carga')->useCurrent();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite' || ! Schema::hasTable('usuario') || ! Schema::hasTable('caso')) {
            return;
        }

        Schema::table('detalle_accidente', function (Blueprint $table): void {
            if (Schema::hasColumn('detalle_accidente', 'Tipo_Trayecto')) {
                $table->dropColumn('Tipo_Trayecto');
            }
        });

        Schema::table('trabajador_accidentado_enfermo', function (Blueprint $table): void {
            $columns = array_values(array_filter(['Tipo_Contrato', 'Tipo_Ingreso', 'Jornada_Laboral'], fn (string $column): bool => Schema::hasColumn('trabajador_accidentado_enfermo', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('caso', function (Blueprint $table): void {
            if (Schema::hasColumn('caso', 'Folio')) {
                $table->dropColumn('Folio');
            }
        });

        Schema::table('empresa_empleador', function (Blueprint $table): void {
            $columns = array_values(array_filter(['RUT_Empresa', 'Actividad_Economica'], fn (string $column): bool => Schema::hasColumn('empresa_empleador', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('rol', function (Blueprint $table): void {
            if (Schema::hasColumn('rol', 'Codigo_Rol')) {
                $table->dropColumn('Codigo_Rol');
            }
        });

        Schema::table('usuario', function (Blueprint $table): void {
            $columns = array_values(array_filter(['Nombre', 'Apellido', 'Cargo', 'Telefono', 'Estado'], fn (string $column): bool => Schema::hasColumn('usuario', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
