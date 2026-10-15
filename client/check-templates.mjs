import { readdirSync, readFileSync } from 'node:fs'
import { dirname, join, relative } from 'node:path'
import { fileURLToPath } from 'node:url'
import { parse } from 'vue/compiler-sfc'

const root = dirname(fileURLToPath(import.meta.url))
let checked = 0
let failed = 0

function check(directory) {
  for (const entry of readdirSync(directory, { withFileTypes: true })) {
    const filename = join(directory, entry.name)
    if (entry.isDirectory()) check(filename)
    else if (filename.endsWith('.vue')) {
      checked++
      const { errors } = parse(readFileSync(filename, 'utf8'), { filename })
      for (const error of errors) {
        failed++
        console.error(
          relative(root, filename) + ': ' + (error.message || error),
        )
      }
    }
  }
}
check(join(root, 'src'))
console.log(checked + ' Vue templates checked; ' + failed + ' errors.')
process.exitCode = failed ? 1 : 0
