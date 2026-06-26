# Moodle Local Plugins View

[![Moodle Plugin CI](https://github.com/jeanlucio/moodle-local_pluginsview/actions/workflows/ci.yml/badge.svg)](https://github.com/jeanlucio/moodle-local_pluginsview/actions/workflows/ci.yml)
[![MDL Shield](https://img.shields.io/endpoint?url=https%3A%2F%2Fmdlshield.com%2Fapi%2Fbadge%2Flocal_pluginsview)](https://mdlshield.com/plugins/local_pluginsview)
![Moodle](https://img.shields.io/badge/Moodle-4.5%2B-orange?style=flat-square&logo=moodle&logoColor=white)
![License](https://img.shields.io/badge/License-GPLv3-blue?style=flat-square)
![Status](https://img.shields.io/badge/Status-Stable-green?style=flat-square)

[English](#english) | [Português](#português)

<details>
<summary><b>📑 Table of Contents</b></summary>

- [✨ Features](#-features)
- [📦 Requirements](#-requirements)
- [🛠️ Installation](#-installation)
- [📖 Usage](#-usage)
- [🧪 Automated Tests](#-automated-tests)
- [📄 License / Licença](#-license--licença)

</details>

---

## English

The **Plugins View** plugin is a read-only local plugin for Moodle that provides non-administrator users (like teachers, coordinators, and researchers) with a consolidated overview of all **additional** installed plugins.

It enriches the local installation data with real-time metadata from the official Moodle Plugins Directory (latest version, release dates, and direct links) without granting access to the full site administration panel.

---

### ✨ Features

* 🔎 **Additional Plugins List:** Displays a clean table of non-standard plugins installed on the site.
* 🌍 **Moodle Directory Integration:** Fetches available updates and release dates directly from the official plugins directory.
* ⚡ **Performance Optimized:** Uses MUC caching and scheduled tasks for directory pre-warming to avoid slow page loads.
* 🎛 **Configurable TTL:** Administrators can configure the cache expiration time dynamically.
* 🔍 **Smart Filtering:** Filter by plugin type, directory status, and search by name or frankenstyle.
* 📊 **Export Options:** Supports native Moodle CSV and custom JSON exports.
* 🔐 **Secure Access:** Protected by its own capability (`local/pluginsview:view`), keeping the rest of the admin panel secure.
* ♿ **Fully Accessible:** Uses Mustache templates and Bootstrap 4/5 standards.

---

### 📦 Requirements

| Component | Version |
|-----------|---------|
| Moodle    | 4.5+    |
| PHP       | 8.1+    |

---

### 🛠️ Installation

1. Download the `.zip` file or clone this repository.
2. Extract the folder into your Moodle `local/` directory.
3. Rename the folder to `pluginsview` (if necessary).
   Final path:
   `your-moodle/local/pluginsview/`
4. Visit **Site administration > Notifications** to complete the installation.

---

### 📖 Usage

1. Grant the `local/pluginsview:view` capability to the desired roles (e.g., Teachers or Managers).
2. Users can access the plugins list via the **More** menu in the primary navigation.
3. (Optional) As a site administrator, go to **Site administration > Plugins > Local plugins > Plugins view** to configure the Cache TTL.

---

### 🧪 Automated Tests

The plugin includes automated tests to ensure stability and correctness when interacting with the Moodle directory.

#### PHPUnit — Unit & Integration Tests

| Test file | Cases | What is covered |
|-----------|------:|----------------|
| `local/pluginsview_manager_test.php` | 4 | Manager: returns only additional plugins excluding core, determines correct status (pending, outdated, up-to-date, not found) based on directory info. |
| `local/api/moodle_directory_api_test.php` | 5 | API client: parsing successful/failed JSON payloads, caching found/not-found results, retry behavior on network unavailability. |
| **Subtotal** | **9** | |

```bash
vendor/bin/phpunit --testsuite local_pluginsview_testsuite
```

#### Behat — Acceptance Tests

| Feature file | Scenarios | What is covered |
|--------------|----------:|----------------|
| `view_plugins.feature` | 1 | Administrator access to the plugins view page, applying search and type filters, clearing filters. |
| **Total** | **1** | |

```bash
php admin/tool/behat/cli/init.php
vendor/bin/behat --tags=@local_pluginsview --profile=chrome
```

---

## Português

O plugin **Visão de Plugins** é um plugin local de leitura (read-only) para Moodle que fornece a usuários não administradores (como professores, coordenadores e pesquisadores) uma visão consolidada de todos os plugins **adicionais** instalados.

Ele enriquece os dados da instalação local com metadados em tempo real do Diretório Oficial de Plugins do Moodle (versão mais recente, datas de lançamento e links diretos) sem conceder acesso ao painel completo de administração.

---

### ✨ Funcionalidades

* 🔎 **Lista de Plugins Adicionais:** Exibe uma tabela limpa dos plugins não-padrão instalados no site.
* 🌍 **Integração com o Diretório:** Busca atualizações disponíveis e datas de lançamento diretamente da API oficial.
* ⚡ **Otimizado para Desempenho:** Utiliza cache MUC e tarefas agendadas para pré-aquecimento dos dados, evitando lentidão.
* 🎛 **TTL Configurável:** Administradores podem configurar o tempo de expiração do cache dinamicamente.
* 🔍 **Filtros Inteligentes:** Filtre por tipo de plugin, status no diretório ou busque por nome e frankenstyle.
* 📊 **Opções de Exportação:** Suporta exportação nativa em CSV e personalizada em JSON.
* 🔐 **Acesso Seguro:** Protegido por uma permissão própria (`local/pluginsview:view`), mantendo o resto do painel de administração bloqueado.
* ♿ **Totalmente Acessível:** Utiliza templates Mustache e padrões do Bootstrap 4/5.

---

### 📦 Requisitos

| Componente | Versão |
|------------|--------|
| Moodle     | 4.5+   |
| PHP        | 8.1+   |

---

### 🛠️ Instalação

1. Baixe o arquivo `.zip` ou clone este repositório.
2. Extraia na pasta `local/` do seu Moodle.
3. Renomeie para `pluginsview` (se necessário).
   Caminho final:
   `seu-moodle/local/pluginsview/`
4. Acesse **Administração do site > Notificações** para concluir a instalação.

---

### 📖 Como Usar

1. Conceda a permissão `local/pluginsview:view` aos papéis desejados (ex.: Professores ou Gerentes).
2. Os usuários poderão acessar a lista de plugins através do menu **Mais** na navegação principal.
3. (Opcional) Como administrador, acesse **Administração do site > Plugins > Plugins locais > Visão de plugins** para configurar o tempo de vida do cache (TTL).

---

### 🧪 Testes Automatizados

O plugin inclui testes automatizados para garantir a estabilidade e o comportamento correto ao consultar o diretório do Moodle.

#### PHPUnit — Testes Unitários e de Integração

| Arquivo de teste | Casos | O que é coberto |
|-----------------|------:|----------------|
| `local/pluginsview_manager_test.php` | 4 | Manager: retorna apenas plugins adicionais ignorando o core, determina status correto (verificando, desatualizado, atualizado, não encontrado) a partir dos dados do diretório. |
| `local/api/moodle_directory_api_test.php` | 5 | Cliente da API: parsing de respostas JSON com sucesso/falha, cacheamento de retornos válidos e 404, comportamento de repetição em caso de falha de rede. |
| **Subtotal** | **9** | |

```bash
vendor/bin/phpunit --testsuite local_pluginsview_testsuite
```

#### Behat — Testes de Aceitação

| Arquivo de teste | Cenários | O que é coberto |
|-----------------|---------:|----------------|
| `view_plugins.feature` | 1 | Acesso do administrador à página de visão de plugins, uso de filtros de busca e de tipo, limpeza de filtros. |
| **Total** | **1** | |

```bash
php admin/tool/behat/cli/init.php
vendor/bin/behat --tags=@local_pluginsview --profile=chrome
```

---

## 📄 License / Licença

This project is licensed under the **GNU General Public License v3 (GPLv3)**.

**Copyright:** 2026 Jean Lúcio
