/**
 * Catalog item for available Git hook checks (from ChecksCatalog).
 */
export interface CatalogEntry {
  id: string;
  label: string;
}

/**
 * Detailed check definition for ecosystem grouping.
 */
export interface Check {
  id: string;
  label: string;
  ecosystem: 'php' | 'js' | 'universal';
  tier: 'primary' | 'secondary';
  requiresConfig: boolean;
  requiresBinary: boolean;
}

/**
 * Hookify Project — the shape ProjectController::show() sends via
 * `$project->only([...])`. Used wherever only the core identity of a
 * project is needed (e.g. GithubRepoPicker).
 */
export interface Project {
  id: number;
  name: string;
  api_token_prefix: string;
  github_repo_id: number | null;
  github_repo_full_name: string | null;
}

/**
 * List-item shape for the Projects index page — everything in Project,
 * plus sync status and enabled check ids, matching what
 * ProjectController::index() sends per project. Enabled checks only ever
 * arrive this way (never nested inside Project itself), since Show.vue
 * receives them as its own separate top-level `enabledIds` prop instead.
 */
export interface ProjectSummary extends Project {
  last_synced_at: string | null;
  enabledIds: string[];
}

/**
 * GitHub Repository item returned from GitHub API.
 */
export interface GitHubRepository {
  id: number;
  full_name: string;
  private: boolean;
}
