import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication, HttpStatus, ValidationPipe,
} from '@nestjs/common';
import { getRepositoryToken } from '@nestjs/typeorm';
import { JwtModule } from '@nestjs/jwt';
import * as bcrypt from 'bcrypt';
import request from 'supertest';
import { App } from 'supertest/types';

import { AuthController } from '../src/auth/auth.controller';
import { AuthService } from '../src/auth/auth.service';
import { JwtAuthGuard } from '../src/auth/jwt-auth.guard';
import { Usuario } from '../src/usuarios/entities/usuario.entity';
import { PendientesController } from '../src/pendientes/pendientes.controller';
import { PendientesService } from '../src/pendientes/pendientes.service';

describe('Gemdy Calendar - Contrato comparativo (e2e)', () => {
  let app: INestApplication<App>;
  let token: string;

  const usuarioId = 21;

  const repositorioUsuarios = {
    findOne: jest.fn(),
  };

  const servicioPendientes = {
    listar: jest.fn(),
    crear: jest.fn(),
  };

  beforeAll(async () => {
    const contrasena = 'PruebaNest1!';
    const contrasenaCifrada = await bcrypt.hash(contrasena, 10);

    repositorioUsuarios.findOne.mockResolvedValue({
      id: usuarioId,
      nombre1: 'Usuario',
      nombre2: null,
      apellido1: 'Prueba',
      apellido2: null,
      correo: 'prueba.nestjs@gemdy.test',
      telefono: null,
      contrasena: contrasenaCifrada,
    });

    servicioPendientes.listar.mockResolvedValue({
      data: [],
      links: {
        first: null,
        last: null,
        prev: null,
        next: null,
      },
      meta: {
        current_page: 1,
        per_page: 5,
        total: 0,
      },
    });

    servicioPendientes.crear.mockImplementation(
      async (_usuarioId, datos) => ({
        id: 190,
        ...datos,
      }),
    );

    const moduleFixture: TestingModule =
      await Test.createTestingModule({
        imports: [
          JwtModule.register({
            secret: 'gemdy-calendar-secret',
          }),
        ],
        controllers: [
          AuthController,
          PendientesController,
        ],
        providers: [
          AuthService,
          JwtAuthGuard,
          {
            provide: getRepositoryToken(Usuario),
            useValue: repositorioUsuarios,
          },
          {
            provide: PendientesService,
            useValue: servicioPendientes,
          },
        ],
      }).compile();

    app = moduleFixture.createNestApplication();

    app.setGlobalPrefix('api');

    app.useGlobalPipes(
      new ValidationPipe({
        whitelist: true,
        transform: true,
        errorHttpStatusCode:
          HttpStatus.UNPROCESSABLE_ENTITY,
      }),
    );

    await app.init();
  });

  it('Login válido devuelve 200 y token JWT', async () => {
    const response = await request(app.getHttpServer())
      .post('/api/login')
      .send({
        correo: 'prueba.nestjs@gemdy.test',
        contrasena: 'PruebaNest1!',
      })
      .expect(200);

    expect(response.body.token).toBeDefined();
    expect(typeof response.body.token).toBe('string');
    expect(response.body.token.length).toBeGreaterThan(0);

    token = response.body.token;
  });

  it('GET protegido devuelve 200 con token válido', async () => {
    expect(token).toBeDefined();

    const response = await request(app.getHttpServer())
      .get('/api/pendientes?page=1&por_pagina=5')
      .set('Authorization', `Bearer ${token}`)
      .expect(200);

    expect(response.body).toHaveProperty('data');
    expect(Array.isArray(response.body.data)).toBe(true);

    expect(servicioPendientes.listar).toHaveBeenCalledWith(
      usuarioId,
      1,
      5,
      undefined,
      undefined,
      undefined,
      undefined,
      false,
      undefined,
    );
  });

  it('POST válido devuelve 201 y Location', async () => {
    expect(token).toBeDefined();

    const datos = {
      usuario_id: usuarioId,
      curso_id: null,
      titulo: 'Estudiar para examen',
      descripcion: 'Repasar los temas del curso.',
      estado: 'pendiente',
      fecha_limite: null,
      hora_pendiente: '18:00:00',
    };

    const response = await request(app.getHttpServer())
      .post('/api/pendientes')
      .set('Authorization', `Bearer ${token}`)
      .send(datos)
      .expect(201);

    expect(response.body.id).toBe(190);
    expect(response.body.titulo).toBe(datos.titulo);

    expect(response.headers.location).toBe(
      '/api/pendientes/190',
    );

    expect(servicioPendientes.crear).toHaveBeenCalledWith(
      usuarioId,
      expect.objectContaining({
        titulo: datos.titulo,
        estado: datos.estado,
      }),
    );
  });

  afterAll(async () => {
    if (app) {
      await app.close();
    }
  });
});