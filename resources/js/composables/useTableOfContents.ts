import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue';

export interface TocItem {
  id: string;
  text: string;
  level: 2 | 3;
  /** The id of the h2 a h3 belongs to; null for h2. */
  parent: string | null;
}

/**
 * Builds an "On this page" outline from the `h2[id]` / `h3[id]` headings of a
 * container and tracks which one the reader is currently at.
 *
 * Ids are written by hand in the template on purpose: they are public anchors
 * (links from the README, from issues), so they must not change when a
 * heading's wording does.
 *
 * @param offset how far from the top of the viewport a heading counts as "reached"
 */
export function useTableOfContents(container: Ref<HTMLElement | null>, offset = 96) {
  const items = ref<TocItem[]>([]);
  const activeId = ref<string | null>(null);

  let headings: HTMLElement[] = [];
  let frame = 0;

  function collect() {
    headings = Array.from(container.value?.querySelectorAll<HTMLElement>('h2[id], h3[id]') ?? []);

    let parent: string | null = null;

    items.value = headings.map((heading) => {
      const level = heading.tagName === 'H2' ? 2 : 3;
      if (level === 2) parent = heading.id;

      return {
        id: heading.id,
        text: heading.textContent?.trim() ?? heading.id,
        level,
        parent: level === 3 ? parent : null,
      };
    });
  }

  function update() {
    frame = 0;
    if (headings.length === 0) return;

    const atBottom =
      window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;

    if (atBottom) {
      activeId.value = headings[headings.length - 1].id;
      return;
    }

    let current = headings[0];
    for (const heading of headings) {
      if (heading.getBoundingClientRect().top - offset > 0) break;
      current = heading;
    }

    activeId.value = current.id;
  }

  function onScroll() {
    if (!frame) frame = requestAnimationFrame(update);
  }

  function goTo(id: string) {
    const target = document.getElementById(id);
    if (!target) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });

    // Keep Inertia's history state: replacing it with null would break the back button.
    window.history.replaceState(window.history.state, '', `#${id}`);
    activeId.value = id;
  }

  onMounted(() => {
    collect();
    update();

    // The ids don't exist yet when the browser first looks for the #hash.
    const hash = decodeURIComponent(window.location.hash.slice(1));
    if (hash) document.getElementById(hash)?.scrollIntoView();

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
  });

  onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);
    if (frame) cancelAnimationFrame(frame);
  });

  return { items, activeId, goTo };
}
