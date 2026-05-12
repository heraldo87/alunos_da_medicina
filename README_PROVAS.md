# Template público de prova - Alunos da Medicina

## Onde colocar no projeto

Copie a pasta `provas/modelo` para a raiz do projeto atual.

A URL ficará assim:

```text
https://alunosdamedicina.com/provas/modelo/
```

Para criar outra prova, duplique a pasta:

```text
/provas/embriologia/
/provas/anatomia/
/provas/histologia/
```

Depois edite o arquivo:

```text
/provas/NOME-DA-PROVA/config.php
```

## Pastas automáticas

- `conteudo/`: PDFs, DOCX, PPTX, imagens, TXT e outros materiais.
- `resumo/`: resumos da prova.
- `podcast/`: áudios MP3, M4A, WAV, OGG ou WEBM.

Basta enviar arquivos para essas pastas. A página lista tudo automaticamente.

## Métricas

Por padrão o template usa arquivo local:

```text
/provas/modelo/storage/analytics.ndjson
```

Ele registra eventos como:

- visitou_pagina
- abriu_conteudo
- baixou_conteudo
- abriu_resumo
- baixou_resumo
- tocou_podcast
- baixou_podcast
- iniciou_quiz
- respondeu_quiz
- finalizou_quiz
- clique_whatsapp

Não usa login, sessão ou cookie. O visitante único é uma aproximação por hash diário de IP + navegador.

## Relatório

Acesse:

```text
/provas/modelo/relatorio.php?token=troque-este-token
```

Troque o token em `config.php` antes de publicar.

## Métricas com banco de dados

O modo padrão é `file`. Se quiser usar banco depois:

1. Execute `database/prova_eventos.sql` no MySQL.
2. Em `config.php`, altere:

```php
'analytics_storage' => 'database',
```

O template tentará usar o `config/database.php` atual do projeto.
