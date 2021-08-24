// Preparation for all your tests can happen here

/**
 * Stub most console methods in tests, apart from debug
 */
global.console = {
    // All logging is stubbed in tests, to suppress implementation calls and minimize noise...
    log: jest.fn(),
    error: jest.fn(),
    warn: jest.fn(),
    info: jest.fn(),

    // ... except fot the native console.debug for, well, debugging
    debug: console.debug,
  };
