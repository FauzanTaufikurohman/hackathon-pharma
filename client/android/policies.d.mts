export function safeFilename(value: string): string;
export function externalWebUrl(value: string, origin: string): string | null;
export function locationErrorCode(code: string): 1 | 2 | 3;
export function expiredReportNames(
  files: { name: string; type: string; mtime: number }[],
  now?: number,
): string[];
