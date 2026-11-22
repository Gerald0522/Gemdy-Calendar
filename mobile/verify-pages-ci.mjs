import { setTimeout } from 'node:timers/promises';

if (process.env.CF_PAGES !== '1' || process.env.CF_PAGES_BRANCH !== 'main') {
    process.exit(0);
}

const commit = process.env.CF_PAGES_COMMIT_SHA;

if (!/^[a-f0-9]{40}$/.test(commit ?? '')) {
    throw new Error('Pages no proporcionó el SHA del commit de producción.');
}

const runsUrl = new URL('https://api.github.com/repos/Gerald0522/Gemdy-Calendar/actions/workflows/ci.yml/runs');
runsUrl.search = new URLSearchParams({ branch: 'main', event: 'push', head_sha: commit, per_page: '1' });
const deadline = Date.now() + 5 * 60_000;

console.log(`Pages espera el resultado de CI para ${commit}.`);

while (Date.now() < deadline) {
    const response = await fetch(runsUrl, {
        headers: { Accept: 'application/vnd.github+json' },
        signal: AbortSignal.timeout(15_000),
    });

    if (!response.ok) {
        throw new Error(`No se pudo verificar GitHub Actions (HTTP ${response.status}). Publicación bloqueada.`);
    }

    const { workflow_runs: runs } = await response.json();
    const run = runs.find((candidate) => candidate.head_sha === commit);

    if (run?.status === 'completed') {
        if (run.conclusion !== 'success') {
            throw new Error(`CI ${run.id} terminó con ${run.conclusion}. Publicación de Pages bloqueada.`);
        }

        console.log(`CI ${run.id} aprobada para ${commit}.`);
        process.exit(0);
    }

    await setTimeout(12_000);
}

throw new Error(`No hay un resultado exitoso de CI a tiempo para ${commit}. Publicación bloqueada.`);
