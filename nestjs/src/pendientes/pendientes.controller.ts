import { Controller, Get, Query, Param, ParseIntPipe, Body, Post, Put, Delete, HttpCode, HttpStatus, Res,
    Req, UseGuards,
 } from '@nestjs/common';

import { PendientesService } from './pendientes.service';
import { UpdatePendienteDto } from './dto/update-pendiente.dto';
import { CreatePendienteDto } from './dto/create-pendiente.dto';
import type { Request, Response } from 'express';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';

@UseGuards(JwtAuthGuard)
@Controller('pendientes')
export class PendientesController {

  constructor(
    private readonly pendientesService: PendientesService,
  ) {}

 @Get()
async listar(
    @Req() request: Request & {
    usuario?: {
      sub: number;
      correo: string;
    };
  },
  @Query('page') page?: string,
  @Query('por_pagina') porPagina?: string,
  @Query('estado') estado?: string,
  @Query('curso_id') cursoId?: string,
  @Query('orden') orden?: string,
  @Query('direccion') direccion?: string,
  @Query('proximos') proximos?: string,
) {
  return this.pendientesService.listar(
    request.usuario!.sub,
    Number(page) || 1,
    Number(porPagina) || 10,
    estado,
    cursoId ? Number(cursoId) : undefined,
    orden,
    direccion,
    proximos == '1',
  );
}

@Get(':id')
async mostrar(
  @Param('id', ParseIntPipe) id: number,
  @Req() request: Request & {
    usuario?: {
      sub: number;
      correo: string;
    };
  },
) {
  return this.pendientesService.mostrar(
    id,
    request.usuario!.sub,
  );
}

@Post()
async crear(
  @Body() datos: CreatePendienteDto,
  @Req() request: Request & {
    usuario?: {
      sub: number;
      correo: string;
    };
  },
  @Res({ passthrough: true }) response: Response,
) {
  const pendiente =
    await this.pendientesService.crear(
      request.usuario!.sub,
      datos,
    );

  response.setHeader(
    'Location',
    `/api/pendientes/${pendiente.id}`,
  );

  return pendiente;
}

@Put(':id')
async actualizar(
  @Param('id', ParseIntPipe) id: number,
  @Body() datos: UpdatePendienteDto,
  @Req() request: Request & {
    usuario?: {
      sub: number;
      correo: string;
    };
  },
) {
  return this.pendientesService.actualizar(
    id,
    request.usuario!.sub,
    datos,
  );
}

@Delete(':id')
@HttpCode(HttpStatus.NO_CONTENT)
async eliminar(
  @Param('id', ParseIntPipe) id: number,
  @Req() request: Request & {
    usuario?: {
      sub: number;
      correo: string;
    };
  },
): Promise<void> {
  await this.pendientesService.eliminar(
    id,
    request.usuario!.sub,
  );
}


}