<?php

namespace Database\Seeders;

use App\Models\CatalogOption;
use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalogs = [
            'case_type' => ['Accidente de trabajo', 'Accidente de trayecto', 'Enfermedad profesional', 'Accidente grave', 'Accidente fatal', 'Accidente de trabajo sin días perdidos', 'Incidente o suceso peligroso', 'Accidente o enfermedad común'],
            'case_status' => ['Borrador', 'Registrado', 'En investigación', 'Con observaciones', 'Seguimiento de medidas', 'Cerrado'],
            'commune' => ['Alto Biobío', 'Antuco', 'Arauco', 'Cabrero', 'Cañete', 'Chiguayante', 'Concepción', 'Contulmo', 'Coronel', 'Curanilahue', 'Florida', 'Hualpén', 'Hualqui', 'Laja', 'Lebu', 'Los Álamos', 'Los Ángeles', 'Lota', 'Mulchén', 'Nacimiento', 'Negrete', 'Penco', 'Quilaco', 'Quilleco', 'San Pedro de la Paz', 'San Rosendo', 'Santa Bárbara', 'Santa Juana', 'Talcahuano', 'Tirúa', 'Tomé', 'Tucapel'],
            'company_type' => ['Principal', 'Contratista', 'Subcontratista', 'Servicios transitorios'],
            'administrator' => ['ACHS', 'IST', 'Mutual de Seguridad', 'ISL'],
            'worker_sex' => ['Mujer', 'Hombre', 'Otro'],
            'yes_no' => ['Sí', 'No'],
            'nationality' => ['Chilena', 'Argentina', 'Boliviana', 'Brasileña', 'Colombiana', 'Ecuatoriana', 'Paraguaya', 'Peruana', 'Uruguaya', 'Venezolana', 'Haitiana', 'Dominicana', 'Cubana', 'Mexicana', 'Otra nacionalidad'],
            'indigenous_people' => ['Ninguno', 'Mapuche', 'Aimara', 'Rapanui', 'Atacameño', 'Quechua', 'Colla', 'Diaguita', 'Alacalufe', 'Yamana (Yagán)', 'Otro'],
            'contract_type' => ['Indefinido', 'Plazo fijo', 'Honorarios', 'Contrata', 'Obra o faena', 'Reemplazo'],
            'income_type' => ['Remuneración fija', 'Remuneración variable', 'Honorarios'],
            'occupational_category' => ['Trabajador dependiente', 'Trabajador independiente', 'Familiar no remunerado', 'Trabajador voluntario'],
            'work_schedule' => ['Diurna', 'Turnos rotativos', 'Nocturna', 'Otra'],
            'route_type' => ['Domicilio - Trabajo', 'Trabajo - Domicilio', 'Entre dos trabajos'],
            'proof_type' => ['Parte de Carabineros', 'Declaración', 'Testigos', 'Fotografías', 'Otro'],
        ];

        if (OriginalGiatepSchema::isActive()) {
            $regionId = DB::table('region')->where('Nombre_Region', 'Región del Biobío')->value('Id_Region');
            if (! $regionId) {
                $regionId = DB::table('region')->insertGetId(['Nombre_Region' => 'Región del Biobío'], 'Id_Region');
            }

            foreach ($catalogs['commune'] as $name) {
                DB::table('comuna')->updateOrInsert(['Nombre_Comuna' => $name], ['Id_Region' => $regionId]);
            }
            foreach ($catalogs['case_type'] as $name) {
                DB::table('tipo_caso')->updateOrInsert(['Nombre_Tipo_Caso' => $name], []);
            }
            foreach (['Ninguno', 'Mapuche', 'Aimara', 'Rapanui', 'Atacameño', 'Quechua', 'Colla', 'Diaguita', 'Alacalufe', 'Yamana (Yagán)', 'Otro'] as $name) {
                DB::table('pueblos_originarios')->updateOrInsert(['Nombre_Pueblo' => $name], []);
            }
            foreach (['Condición insegura', 'Acto inseguro', 'Contacto con energía', 'Caída de personas', 'Exposición a agentes'] as $name) {
                DB::table('evento_que_provoco_el_accidente')->updateOrInsert(['Nombre_Evento' => $name], []);
            }

            return;
        }

        foreach ($catalogs as $category => $options) {
            foreach ($options as $index => $option) {
                CatalogOption::updateOrCreate(
                    ['category' => $category, 'value' => $option],
                    ['label' => $option, 'sort_order' => $index + 1, 'is_active' => true],
                );
            }
        }
    }
}
