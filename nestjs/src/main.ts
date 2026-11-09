import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { HttpStatus, ValidationPipe } from '@nestjs/common';
import { BusinessRuleExceptionFilter } from './common/filters/business-rule-exception.filter';
import { DocumentBuilder, SwaggerModule } from '@nestjs/swagger';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);
  app.setGlobalPrefix('api');
  app.useGlobalFilters(
  new BusinessRuleExceptionFilter(),
  );
  
  app.useGlobalPipes(
  new ValidationPipe({
    whitelist: true,
    transform: true,
    errorHttpStatusCode: HttpStatus.UNPROCESSABLE_ENTITY,
  }),
);

  const config = new DocumentBuilder()
    .setTitle('Gemdy Calendar API')
    .setDescription('API REST para la gestión de pendientes de Gemdy Calendar.')
    .setVersion('1.0.0')
    .addBearerAuth()
    .build();

  const document = SwaggerModule.createDocument(app, config);

  SwaggerModule.setup('api/docs', app, document);
  await app.listen(process.env.PORT ?? 3000);
}
void bootstrap();
