import { Module } from '@nestjs/common';
import { AppController } from './app.controller';
import { AppService } from './app.service';

import { TypeOrmModule } from '@nestjs/typeorm';
import { join } from 'path';
import { PendientesModule } from './pendientes/pendientes.module';
import { AuthModule } from './auth/auth.module';

@Module({
  imports: [
    TypeOrmModule.forRoot({
      type: 'better-sqlite3',

      database: join(
        process.cwd(),
        '..',
        'database',
        'database.sqlite',
      ),

      fileMustExist: true,

      autoLoadEntities: true,

      synchronize: false,

      migrationsRun: false,

      logging: false,
    }),

    PendientesModule,
    AuthModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}