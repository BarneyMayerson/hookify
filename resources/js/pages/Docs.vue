<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import CodeBlock from '@/components/Docs/CodeBlock.vue';
import OnThisPage from '@/components/Docs/OnThisPage.vue';
import { useTableOfContents } from '@/composables/useTableOfContents';
import { login } from '@/routes';

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

const description =
  'How to set up Hookify: create a project, pick checks, sync Git hooks with one command, and keep them up to date across your team.';

// The outline is built from the h2/h3 headings (with an id) inside the article.
const article = ref<HTMLElement | null>(null);
const { items, activeId, goTo } = useTableOfContents(article);

// Keep the output examples in sync with what hookify-cli really prints.
// !!! Wrapped in ref(): without it vue-tsc reports a bogus "Property 'value' does not exist" in the template.
const snippets = ref({
  syncOutput: `Hookify · my-project
✔ Config fetched from hookify.to
✔ pre-commit hook installed (3 checks)
  • pint
  • phpstan
  • oxfmt

1 hook active.`,
  hookFailure: `✔ pint
✖ phpstan
   ------ ------------------------------
    Line   app/Foo.php
   ------ ------------------------------
    12     Undefined variable: $user

phpstan failed. Commit aborted.`,
  missingTool: `✖ oxlint is not installed
  Fix: npm install -D oxlint
  Or disable this check in the Hookify dashboard and run: npx hookify-cli sync`,
});
</script>

<template>
  <Head title="Documentation">
    <meta head-key="description" name="description" :content="description" />
    <meta head-key="og:title" property="og:title" content="Hookify documentation" />
    <meta head-key="og:description" property="og:description" :content="description" />
  </Head>

  <div class="mx-auto max-w-5xl px-6 py-10">
    <!-- On small screens the outline is a collapsible block above the article -->
    <details class="border-border mb-8 rounded-lg border px-4 py-3 xl:hidden">
      <summary class="cursor-pointer text-sm font-semibold uppercase">On this page</summary>
      <div class="mt-3">
        <OnThisPage :items :active-id @navigate="goTo" />
      </div>
    </details>

    <div class="xl:grid xl:grid-cols-[minmax(0,1fr)_15rem] xl:gap-12">
      <article
        ref="article"
        class="prose prose-neutral dark:prose-invert prose-headings:scroll-mt-8 max-w-none min-w-0"
      >
        <h1>Documentation</h1>

        <p>
          Hookify keeps Git hooks in one place and syncs them to every developer on your team. You
          pick checks from a fixed catalog in the dashboard, and a single CLI command writes the
          matching hooks into each repository.
        </p>

        <h2 id="getting-started">Getting started</h2>
        <ol>
          <li>Sign in at <a :href="login().url">hookify.to</a> with your GitHub account.</li>
          <li>
            Create a project. Hookify shows its token <strong>once</strong>, together with the
            command to run.
          </li>
          <li>Open the project's <strong>Settings</strong> and enable the checks you need.</li>
          <li>Run the command in the root of your repository:</li>
        </ol>
        <CodeBlock code="HOOKIFY_TOKEN=hk_live_... npx hookify-cli sync" />
        <p>
          Hooks live in each clone's <code>.git/hooks</code>, so every developer runs
          <code>sync</code> once in their own clone, and again after the checks change in the
          dashboard.
        </p>

        <h2 id="cli">CLI</h2>
        <p>
          The CLI is a separate, open source package:
          <a href="https://github.com/BarneyMayerson/hookify-cli" target="_blank" rel="noopener"
            >hookify-cli</a
          >. There is nothing to install: run it with <code>npx</code>. With
          <code>npm install -g hookify-cli</code> the command is also available as
          <code>hookify</code>.
        </p>

        <h3 id="sync">sync</h3>
        <p>
          Fetches the project's current configuration and writes the matching hooks into
          <code>.git/hooks</code>.
        </p>
        <CodeBlock :code="snippets.syncOutput" />
        <ul>
          <li>
            Writes only the hook types that have checks enabled, and removes a hook again when you
            disable all of its checks.
          </li>
          <li>
            Never overwrites a hook it doesn't own. A foreign hook (Husky, for example) is backed up
            to <code>pre-commit.backup.&lt;timestamp&gt;</code> first.
          </li>
          <li>
            Works offline: if the API can't be reached, it uses the last cached configuration and
            says so.
          </li>
          <li>
            Notices a dead token. On a 401 it removes the hooks it installed, clears its cache and
            exits with an error. See
            <a href="#token-no-longer-valid" @click.prevent="goTo('token-no-longer-valid')"
              >troubleshooting</a
            >.
          </li>
          <li>Refuses to run in a monorepo.</li>
        </ul>

        <h3 id="hooks-on-commit">What the hook does on commit</h3>
        <p>The installed hooks are plain shell scripts. On every commit they:</p>
        <ol>
          <li>
            Check that every tool is installed, before running anything, and report all the missing
            ones at once.
          </li>
          <li>Run the checks one by one and stop at the first failure.</li>
          <li>
            Stay quiet when a check passes: one line per check, with the time if it took a second or
            more. A failing check shows its own output.
          </li>
        </ol>
        <CodeBlock :code="snippets.hookFailure" />
        <p>To skip the hooks for a single commit, use <code>git commit --no-verify</code>.</p>

        <h3 id="configuration">Configuration</h3>
        <table>
          <thead>
            <tr>
              <th>Variable or flag</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>HOOKIFY_TOKEN</code></td>
              <td>Required for <code>sync</code>. The project token.</td>
            </tr>
            <tr>
              <td><code>HOOKIFY_API</code></td>
              <td>
                API base URL. Defaults to <code>https://hookify.to/api/v1</code>. Needed only for a
                self-hosted instance.
              </td>
            </tr>
            <tr>
              <td><code>--quiet</code>, <code>-q</code></td>
              <td>Print only warnings and errors. Useful in CI and install scripts.</td>
            </tr>
            <tr>
              <td><code>--verbose</code>, <code>-v</code></td>
              <td>Also print the command each check runs.</td>
            </tr>
            <tr>
              <td><code>--version</code></td>
              <td>Print the CLI version.</td>
            </tr>
            <tr>
              <td><code>NO_COLOR</code></td>
              <td>Turn colors off. They are off anyway when the output isn't a terminal.</td>
            </tr>
          </tbody>
        </table>
        <p>Keep the token out of version control. In CI, store it as a secret.</p>

        <h3 id="analyze">analyze</h3>
        <p>
          Read-only. Reports what ESLint, Prettier and OXC tooling are present, whether anything
          actually runs them, and whether a git hook calls a tool that isn't installed.
        </p>
        <CodeBlock code="npx hookify-cli analyze" />
        <p>
          It never writes files, never installs anything, and never talks to the network. “Probably
          unused” is a prompt to verify, not a verdict.
        </p>

        <h3 id="migrate-oxfmt">migrate oxfmt</h3>
        <p>
          Migrates a Prettier setup to oxfmt on a new local branch, shows exactly what will be
          committed, and commits only if you confirm. If you decline or a step fails, everything is
          rolled back.
        </p>
        <CodeBlock code="npx hookify-cli migrate oxfmt" />
        <p>
          It never pushes, never removes Prettier and never reformats your codebase: run
          <code>npx oxfmt</code> yourself and commit that separately. There is no automatic
          migration for ESLint: <code>@oxlint/migrate</code> often produces a large config and skips
          many rules, and <code>analyze</code> will point you at the upstream tool when it is
          relevant.
        </p>

        <h2 id="projects">Projects and tokens</h2>
        <p>
          Each project has its own token. Hookify stores only a hash of it, so the full token is
          shown once, right after the project is created. The project page and the project card show
          the token's prefix and when the project last synced.
        </p>

        <h3 id="regenerate-token">Regenerating a token</h3>
        <p>
          If a token is lost or leaked, open the project's <strong>Settings</strong> and choose
          <strong>Regenerate token</strong>. A new token is shown once, and the old one stops
          working immediately. Update it wherever it was used: in your teammates' environments and
          in CI secrets. Anyone who runs <code>sync</code> with the old token gets an error. Their
          installed hooks keep working, but won’t update until they use the new token.
        </p>

        <h3 id="deleting-a-project">Deleting a project</h3>
        <p>
          Deleting a project revokes its token. Hooks already installed keep running locally, since
          they don't contact Hookify, and the next <code>sync</code> removes them.
        </p>

        <h2 id="github-access">GitHub access</h2>
        <ul>
          <li>
            Signing in asks GitHub only for your public profile and email address, read-only. Your
            name and email are refreshed from GitHub every time you sign in. To change them, change
            them on GitHub and sign in again.
          </li>
          <li>
            Linking a repository to a project is optional. The picker lists your public repositories
            by default. To include private ones, press
            <strong>Include private repositories</strong>: only then does Hookify ask GitHub for the
            broader <code>repo</code> scope, because GitHub OAuth Apps have no read-only scope for
            private repositories. Hookify uses it only to list your repositories.
          </li>
          <li>
            The GitHub access token is stored encrypted. You can revoke Hookify's access at any time
            under Settings → Applications on GitHub.
          </li>
          <li>
            If you don't see an organization's repository, an owner of the organization may need to
            approve Hookify on GitHub.
          </li>
        </ul>

        <h2 id="how-checks-work">How checks work</h2>
        <p>
          Users toggle ready-made checks on and off; there is deliberately no way to enter a custom
          command. The catalog (<code>App\Support\ChecksCatalog</code>) is the single source of
          truth for each check's command, its hook type and whether it needs something the project
          doesn't ship with, such as a config file or an external binary like
          <code>gitleaks</code>. A project's enabled checks are stored in
          <code>project_rules</code>, and the <code>/api/v1/manifest</code> endpoint turns them into
          a versioned JSON manifest that the CLI uses to write the hooks. A CLI that doesn't know
          the manifest's schema version refuses to use it.
        </p>

        <h2 id="self-hosting">Self-hosting</h2>
        <p>
          Hookify is open source. To use your own instance, point the CLI at it. The dashboard of an
          instance adds <code>HOOKIFY_API</code> to the commands it shows automatically, based on
          its <code>APP_URL</code>.
        </p>
        <CodeBlock
          code="HOOKIFY_TOKEN=hk_live_... HOOKIFY_API=https://your-hookify-instance/api/v1 npx hookify-cli sync"
        />
        <p>
          Only point <code>HOOKIFY_API</code> at a server you trust: the server decides which
          commands the generated hooks run on your machine. See the
          <a href="https://github.com/BarneyMayerson/hookify" target="_blank" rel="noopener"
            >main repository</a
          >
          for how to run an instance.
        </p>

        <h2 id="limitations">Known limitations</h2>
        <ul>
          <li>
            Monorepos are not supported yet. <code>sync</code> stops when it finds more than one
            manifest.
          </li>
          <li>
            Hooks are installed into <code>.git/hooks</code>. If your repository sets
            <code>core.hooksPath</code> (Husky does), Git ignores that directory and the Hookify
            hooks won't run until you remove the setting.
          </li>
          <li>
            Hooks are POSIX <code>sh</code> scripts. On Windows they run through the
            <code>sh</code> that comes with Git for Windows.
          </li>
        </ul>

        <h2 id="troubleshooting">Troubleshooting</h2>

        <h3 id="token-no-longer-valid">“This project token is no longer valid”</h3>
        <p>
          The token was regenerated, the project was deleted, or the token belongs to another server
          (a development token used against hookify.to, for example). <code>sync</code> removes the
          hooks it installed. Copy the current token from the project's page, or regenerate it in
          the project's Settings, and run <code>sync</code> again.
        </p>

        <h3 id="tool-not-installed">“… is not installed”</h3>
        <p>
          A check's tool is missing in this project, so the commit is stopped before anything runs.
          Install it as the message says, or disable the check in the dashboard and run
          <code>sync</code> again.
        </p>
        <CodeBlock :code="snippets.missingTool" />

        <h3 id="hooks-do-not-run">The hooks don't run</h3>
        <p>
          Check that <code>core.hooksPath</code> isn't set (<code>git config core.hooksPath</code>),
          that <code>sync</code> was run in this clone, and that the commit wasn't made with
          <code>--no-verify</code>.
        </p>

        <h3 id="wrong-server">sync talks to the wrong server</h3>
        <p>
          Versions before 0.2 used a different default server, and <code>npx</code> may keep an old
          copy in its cache. Run the latest version and check which one you got:
        </p>
        <CodeBlock code="npx hookify-cli@latest --version" />

        <h2 id="security">Security</h2>
        <ul>
          <li>Project tokens are shown once and stored only as a hash.</li>
          <li>
            Hook commands come only from the fixed catalog, never from user input. Check ids and
            tool names are validated before they are placed into a hook, and project names are
            sanitized.
          </li>
          <li>
            Generated hook scripts never contact Hookify. Only <code>sync</code> talks to the API.
          </li>
          <li>
            Hooks run commands on your machine at every commit, so use only a server you trust.
          </li>
          <li>The project token is never written to disk or printed by the CLI.</li>
        </ul>

        <h2 id="requirements">Requirements</h2>
        <ul>
          <li>Node.js ≥ 20</li>
          <li>Git</li>
          <li>A Hookify account and project</li>
        </ul>

        <p>
          For the full details see the
          <a href="https://github.com/BarneyMayerson/hookify" target="_blank" rel="noopener">
            main repository
          </a>
          and the
          <a href="https://github.com/BarneyMayerson/hookify-cli" target="_blank" rel="noopener">
            CLI repository </a
          >.
        </p>
      </article>

      <aside class="hidden xl:block">
        <div class="sticky top-8 max-h-[calc(100vh-4rem)] overflow-y-auto pb-8">
          <OnThisPage :items :active-id @navigate="goTo" />
        </div>
      </aside>
    </div>
  </div>
</template>
