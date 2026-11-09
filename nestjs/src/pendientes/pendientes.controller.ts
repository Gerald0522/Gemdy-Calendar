
import {
  Controller,
  Get,
  Query,
  Param,
  ParseIntPipe,
  Body,
  Post,
  Put,
  Delete,
  HttpCode,
  HttpStatus,
  Res,
  Req,
  UseGuards,
} from '@nestjs/common';

import { PendientesService } from './pendientes.service';
import { UpdatePendienteDto } from './dto/update-pendiente.dto';
import { CreatePendienteDto } from './dto/create-pendiente.dto';
import type { Request, Response } from 'express';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';
import {
  ApiQuery,
  ApiBearerAuth,
  ApiOperation,
  ApiResponse,
} from '@nestjs/swagger';

@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('pendientes')
export class PendientesController {

  constructor(
    private readonly pendientesService: PendientesService,
  ) {}

  @Get()
  @ApiOperation({ summary: 'Listar pendientes del usuario autenticado' })
  @ApiResponse({ status: 200, description: 'Pendientes obtenidos correctamente.' })
  @ApiResponse({ status: 401, description: 'Usuario no autenticado.' })
  @ApiQuery({ name: 'page', required: false, type: Number })
  @ApiQuery({ name: 'por_pagina', required: false, type: Number })
  @ApiQuery({ name: 'estado', required: false, type: String })
  @ApiQuery({ name: 'curso_id', required: false, type: Number })
  @ApiQuery({ name: 'orden', required: false, type: String })
  @ApiQuery({ name: 'direccion', required: false, type: String })
  @ApiQuery({ name: 'proximos', required: false, type: String })
  @ApiQuery({
    name: 'buscar',
    required: false,
    type: String,
    description: 'Buscar pendientes por título',
  })
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
    @Query('buscar') buscar?: string,
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
      buscar,
    );
  }

  @Get(':id')
  @ApiOperation({ summary: 'Consultar un pendiente por ID' })
  @ApiResponse({ status: 200, description: 'Pendiente encontrado.' })
  @ApiResponse({ status: 400, description: 'ID inválido.' })
  @ApiResponse({ status: 401, description: 'Usuario no autenticado.' })
  @ApiResponse({ status: 403, description: 'No tiene permiso para consultar este pendiente.' })
  @ApiResponse({ status: 404, description: 'Pendiente no encontrado.' })
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
  @ApiOperation({ summary: 'Crear un nuevo pendiente' })
  @ApiResponse({ status: 201, description: 'Pendiente creado correctamente.' })
  @ApiResponse({ status: 401, description: 'Usuario no autenticado.' })
  @ApiResponse({ status: 409, description: 'Se incumplió una regla de negocio.' })
  @ApiResponse({ status: 422, description: 'Datos inválidos o curso inexistente.' })
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
  @ApiOperation({ summary: 'Actualizar un pendiente existente' })
  @ApiResponse({ status: 200, description: 'Pendiente actualizado correctamente.' })
  @ApiResponse({ status: 400, description: 'ID inválido.' })
  @ApiResponse({ status: 401, description: 'Usuario no autenticado.' })
  @ApiResponse({ status: 403, description: 'No tiene permiso para actualizar este pendiente.' })
  @ApiResponse({ status: 404, description: 'Pendiente no encontrado.' })
  @ApiResponse({ status: 409, description: 'Se incumplió una regla de negocio.' })
  @ApiResponse({ status: 422, description: 'Datos inválidos.' })
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
  @ApiOperation({ summary: 'Eliminar un pendiente' })
  @ApiResponse({ status: 204, description: 'Pendiente eliminado correctamente.' })
  @ApiResponse({ status: 400, description: 'ID inválido.' })
  @ApiResponse({ status: 401, description: 'Usuario no autenticado.' })
  @ApiResponse({ status: 403, description: 'No tiene permiso para eliminar este pendiente.' })
  @ApiResponse({ status: 404, description: 'Pendiente no encontrado.' })
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
