import { Test, TestingModule } from '@nestjs/testing';
import {
  INestApplication,
  HttpStatus,
  ValidationPipe,
} from '@nestjs/common';
import request from 'supertest';
import { App } from 'supertest/types';
import { AppModule } from './../src/app.module';
import { BusinessRuleExceptionFilter } from '../src/common/filters/business-rule-exception.filter';

describe('Gemdy Calendar - Contrato comparativo (e2e)', () => {
  let app: INestApplication<App>;
  let token: string;
  let pendienteCreadoId: number | null = null;

  beforeAll(async () => {
    const moduleFixture: TestingModule =
      await Test.createTestingModule({
        imports: [AppModule],
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

    app.useGlobalFilters(
      new BusinessRuleExceptionFilter(),
    );

    await app.init();

    const loginResponse = await request(
      app.getHttpServer(),
    )
      .post('/api/login')
      .send({
        correo: 'prueba.nestjs@gemdy.test',
        contrasena: 'PruebaNest1!',
      })
      .expect(200);

    token = loginResponse.body.token;
  });

  it('usuario puede listar sus pendientes', async () => {
    const response = await request(
      app.getHttpServer(),
    )
      .get('/api/pendientes?page=1&por_pagina=5')
      .set('Authorization', `Bearer ${token}`)
      .expect(200);

    expect(response.body).toHaveProperty('data');
    expect(response.body).toHaveProperty('links');
    expect(response.body).toHaveProperty('meta');
    expect(Array.isArray(response.body.data)).toBe(true);
  });

  it('usuario puede crear pendiente', async () => {
    const response = await request(
      app.getHttpServer(),
    )
      .post('/api/pendientes')
      .set('Authorization', `Bearer ${token}`)
      .send({
        usuario_id: 21,
        curso_id: null,
        titulo: 'Estudiar para examen',
        descripcion: 'Repasar los temas del curso.',
        estado: 'pendiente',
        fecha_limite: null,
        hora_pendiente: '18:00:00',
      })
      .expect(201);

    pendienteCreadoId = response.body.id;

    expect(response.body.titulo).toBe(
      'Estudiar para examen',
    );

    expect(response.headers.location).toBeDefined();
  });

  it('crear pendiente valida campos obligatorios', async () => {
    const response = await request(
      app.getHttpServer(),
    )
      .post('/api/pendientes')
      .set('Authorization', `Bearer ${token}`)
      .send({})
      .expect(422);

    expect(response.body.statusCode).toBe(422);
    expect(response.body.message).toBeDefined();
  });

  afterAll(async () => {
    if (pendienteCreadoId !== null) {
      await request(app.getHttpServer())
        .delete(
          `/api/pendientes/${pendienteCreadoId}`,
        )
        .set('Authorization', `Bearer ${token}`);
    }

    await app.close();
  });
});