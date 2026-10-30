<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Home',
        href: '/',
      },
      {
        title: 'Documentation',
        href: '/docs',
      },
    ],
  },
});
</script>

<template>
  <Head title="Documentation" />

  <div class="mx-auto max-w-5xl px-6 py-10">
    <article class="prose prose-neutral dark:prose-invert max-w-none">
      <h1>Documentation</h1>

      <p>
        Hookify is a web application and dashboard for configuring project-specific Git hooks. It
        lets a team pick checks from a fixed catalog and keeps every repository’s local hooks in
        sync with a single CLI command.
      </p>

      <h2>CLI Client</h2>
      <p>
        The CLI lives in a separate package:
        <a href="https://github.com/BarneyMayerson/hookify-cli" target="_blank" rel="noopener">
          hookify-cli </a
        >.
      </p>

      <h3>sync</h3>
      <p>
        Fetches the current check configuration and writes the matching hooks into
        <code>.git/hooks</code>.
      </p>
      <pre><code>HOOKIFY_TOKEN=hk_live_... HOOKIFY_API=https://your-hookify-instance/api/v1 npx hookify-cli sync</code></pre>

      <ul>
        <li>Writes only the hook types that have checks enabled.</li>
        <li>Never overwrites a hook it doesn’t own (backs up foreign hooks).</li>
        <li>Works offline using the last cached manifest.</li>
        <li>Removes its hooks and clears the cache if the token is revoked (401).</li>
        <li>Refuses to run in a monorepo.</li>
      </ul>

      <h3>analyze</h3>
      <p>
        Read-only. Reports what ESLint, Prettier and OXC tooling are present and whether anything
        actually runs them.
      </p>
      <pre><code>npx hookify-cli analyze</code></pre>
      <p>
        It never writes files, never installs anything, and never talks to the network. “Probably
        unused” is a prompt to verify, not a verdict.
      </p>

      <h3>migrate oxfmt</h3>
      <p>Migrates a Prettier setup to oxfmt on a new local branch.</p>
      <pre><code>npx hookify-cli migrate oxfmt</code></pre>
      <p>
        There is no automatic migration for ESLint. <code>@oxlint/migrate</code> often produces a
        large config and skips many rules. <code>analyze</code> will point you at the upstream tool
        when relevant.
      </p>

      <h2>How checks work</h2>
      <p>
        <code>App\Support\ChecksCatalog</code> is the single source of truth for every available
        check. A project’s enabled checks live in <code>project_rules</code>. The
        <code>/api/v1/manifest</code> endpoint turns that into the JSON the CLI consumes.
      </p>

      <h2>Security</h2>
      <ul>
        <li>Project tokens are shown once and stored only as a hash.</li>
        <li>
          Generated hook scripts never contact Hookify. Only <code>sync</code> talks to the API.
        </li>
        <li>Project names are sanitized before being embedded in hook files.</li>
      </ul>

      <h2>Requirements</h2>
      <ul>
        <li>Node.js ≥ 20</li>
        <li>Git</li>
        <li>A Hookify account and project</li>
      </ul>

      <p>
        For the full details see the
        <a href="https://github.com/BarneyMayerson/hookify" target="_blank" rel="noopener"
          >main repository</a
        >
        and the
        <a href="https://github.com/BarneyMayerson/hookify-cli" target="_blank" rel="noopener"
          >CLI repository</a
        >.
      </p>
    </article>
  </div>
</template>
