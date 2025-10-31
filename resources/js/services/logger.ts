const getTimestamp = () => new Date().toISOString();

const logger = {
    info: (...args: unknown[]) => {
        // eslint-disable-next-line no-console
        console.log(`%c[CLIENT INFO] %c${getTimestamp()}`, 'color: #0ea5e9; font-weight: bold;', 'color: #9ca3af;', ...args);
    },
    warn: (...args: unknown[]) => {
        // eslint-disable-next-line no-console
        console.warn(`%c[CLIENT WARN] %c${getTimestamp()}`, 'color: #f59e0b; font-weight: bold;', 'color: #9ca3af;', ...args);
    },
    error: (...args: unknown[]) => {
        // eslint-disable-next-line no-console
        console.error(`%c[CLIENT ERROR] %c${getTimestamp()}`, 'color: #ef4444; font-weight: bold;', 'color: #9ca3af;', ...args);
    },
    debug: (...args: unknown[]) => {
        // eslint-disable-next-line no-console
        console.debug(`%c[CLIENT DEBUG] %c${getTimestamp()}`, 'color: #8b5cf6; font-weight: bold;', 'color: #9ca3af;', ...args);
    },
};

export default logger;
