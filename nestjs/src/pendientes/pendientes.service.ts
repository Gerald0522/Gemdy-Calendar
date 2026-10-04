import { Injectable, NotFoundException, UnprocessableEntityException, ForbiddenException} from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { CreatePendienteDto } from './dto/create-pendiente.dto';
import { BusinessRuleException } from '../common/exceptions/business-rule.exception';

import { Pendiente } from './entities/pendiente.entity';
import { UpdatePendienteDto } from './dto/update-pendiente.dto';
import { Curso } from '../cursos/entities/curso.entity';

@Injectable()
export class PendientesService {
  constructor(
    @InjectRepository(Pendiente)
    private readonly pendienteRepository: Repository<Pendiente>,

    @InjectRepository(Curso)
    private readonly cursoRepository: Repository<Curso>,
  ) {}

  async listar(
  usuarioId: number,
  page: number = 1,
  perPage: number = 10,
  estado?: string,
  cursoId?: number,
  orden?: string,
  direccion?: string,
  proximos?: boolean,
) {
  page = Math.max(1, page);
  perPage = Math.min(Math.max(1, perPage), 50);

  const query =
    this.pendienteRepository.createQueryBuilder(
      'pendiente',
    );

  
  query.where(
    'pendiente.usuario_id = :usuarioId',
    { usuarioId },
  );

 
  if (estado) {
    query.andWhere(
      'pendiente.estado = :estado',
      { estado },
    );
  }


  if (cursoId) {
    query.andWhere(
      'pendiente.curso_id = :cursoId',
      { cursoId },
    );
  }


  if (proximos) {
    const hoy = new Date()
      .toISOString()
      .split('T')[0];

    query.andWhere(
      'pendiente.fecha_limite IS NOT NULL',
    );

    query.andWhere(
      'pendiente.fecha_limite >= :hoy',
      { hoy },
    );
  }

  const camposPermitidos = [
    'fecha_limite',
    'titulo',
    'estado',
    'created_at',
  ];

  const campoOrden =
    orden && camposPermitidos.includes(orden)
      ? orden
      : 'fecha_limite';

  const direccionOrden: 'ASC' | 'DESC' =
    direccion === 'desc' ? 'DESC' : 'ASC';

  query.orderBy(
    `pendiente.${campoOrden}`,
    direccionOrden,
  );

  query.skip(
    (page - 1) * perPage,
  );

  query.take(perPage);

  const [pendientes, total] =
    await query.getManyAndCount();


    const lastPage = Math.max(
  1,
  Math.ceil(total / perPage),
);

return {
  data: pendientes,

  links: {
    first: `/api/pendientes?page=1&por_pagina=${perPage}`,
    last: `/api/pendientes?page=${lastPage}&por_pagina=${perPage}`,
    prev:
      page > 1
        ? `/api/pendientes?page=${page - 1}&por_pagina=${perPage}`
        : null,
    next:
      page < lastPage
        ? `/api/pendientes?page=${page + 1}&por_pagina=${perPage}`
        : null,
  },

  meta: {
    current_page: page,
    per_page: perPage,
    total: total,
    last_page: lastPage,
  },
}; 
}

  async mostrar(id: number, usuarioId: number, ): Promise<Pendiente> {
    const pendiente =
      await this.pendienteRepository.findOne({
        where: { id },
      });

    if (!pendiente) {
      throw new NotFoundException(
        `No se encontró el pendiente con id ${id}.`,
      );
    }
    if (pendiente.usuario_id !== usuarioId) {
    throw new ForbiddenException(
      'No tiene permiso para acceder a este pendiente.',
    );
  }

    return pendiente;
  }

   async crear(usuarioId: number, datos: CreatePendienteDto,
    ): Promise<Pendiente> {
      const datosConUsuario = {
        ...datos,
        usuario_id: usuarioId,
      };

      await this.validarCursoDelUsuario(
        datosConUsuario,
      );

      this.validarFechaLimite(
        datosConUsuario,
      );

      const pendiente =
        this.pendienteRepository.create(
          datosConUsuario,
        );

      return this.pendienteRepository.save(
        pendiente,
      );
    }
  async actualizar( id: number, usuarioId: number, datos: UpdatePendienteDto,
  ): Promise<Pendiente> {
    const pendiente = await this.mostrar(id, usuarioId);

    const datosDefinidos = Object.fromEntries(
      Object.entries(datos).filter(
        ([, valor]) => valor !== undefined,
      ),
    );

    const datosCompletos = {
      ...pendiente,
      ...datosDefinidos,
    };

    await this.validarCursoDelUsuario(
      datosCompletos,
    );

    this.validarFechaLimite(datosCompletos);

    Object.assign(pendiente, datosDefinidos);

    return this.pendienteRepository.save(
      pendiente,
    );
  }

  async eliminar(id: number, usuarioId: number): Promise<void> {
    const pendiente = await this.mostrar(id, usuarioId, );

    await this.pendienteRepository.remove(
      pendiente,
    );
  }

  private async validarCursoDelUsuario(
    datos: CreatePendienteDto,
  ): Promise<void> {
    if (!datos.curso_id) {
      return;
    }

    const curso =
      await this.cursoRepository.findOne({
        where: { id: datos.curso_id },
      });

    if (!curso) {
  throw new UnprocessableEntityException({
    message: [
      'El curso seleccionado no existe.',
    ],
    error: 'Unprocessable Entity',
    statusCode: 422,
  });
}

    if (curso.usuario_id !== datos.usuario_id) {
      throw new BusinessRuleException(
        'El curso seleccionado no pertenece al usuario indicado.',
      );
    }
  }

  private validarFechaLimite(
    datos: CreatePendienteDto,
  ): void {
    if (!datos.fecha_limite) {
      return;
    }

    const fechaLimite = new Date(
      `${datos.fecha_limite}T00:00:00`,
    );

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    if (fechaLimite < hoy) {
      throw new BusinessRuleException(
        'La fecha límite del pendiente no puede ser anterior a la fecha actual.',
      );
    }
  }
}