import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { HttpStatus, ValidationPipe } from '@nestjs/common';
import { BusinessRuleExceptionFilter } from './common/filters/business-rule-exception.filter';

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
  await app.listen(process.env.PORT ?? 3000);
}
void bootstrap();
