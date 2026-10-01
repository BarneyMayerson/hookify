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
 * GitHooks Hub Project model.
 */
export interface Project {
  id: number;
  name: string;
  api_token_prefix: string;
  github_repo_id: number | null;
  github_repo_full_name: string | null;
  last_synced_at: string | null;
  enabledIds?: string[];
  created_at?: string;
  updated_at?: string;
}

/**
 * GitHub Repository item returned from GitHub API.
 */
export interface GitHubRepository {
  id: number;
  full_name: string;
  private: boolean;
}
