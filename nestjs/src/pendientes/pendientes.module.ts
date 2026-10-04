import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';

import { Pendiente } from './entities/pendiente.entity';
import { Curso } from '../cursos/entities/curso.entity';
import { PendientesService } from './pendientes.service';
import { PendientesController } from './pendientes.controller';
import { AuthModule } from '../auth/auth.module';

@Module({
  imports: [
    TypeOrmModule.forFeature([
      Pendiente,
      Curso,
    ]),
    AuthModule,
  ],
  controllers: [PendientesController],
  providers: [PendientesService],
})
export class PendientesModule {}