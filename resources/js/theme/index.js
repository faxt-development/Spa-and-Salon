import { createTheme } from '@mui/material/styles';

/**
 * Creates a Material-UI theme based on the provided color scheme
 * @param {Object} colors - Object containing color values
 * @returns {Object} Material-UI theme object
 */
export const createAppTheme = (colors = {}) => {
  // Default colors that match your Tailwind config
  const defaultColors = {
    primary: {
      main: '#8B9259', // Your primary green
      light: '#9fa97a',
      dark: '#5f6b3c',
      contrastText: '#ffffff',
    },
    secondary: {
      main: '#EDDFC0', // Your secondary color
      light: '#f2e9d5',
      dark: '#d4c8a9',
      contrastText: '#1F4B48',
    },
    accent: {
      main: '#F4C96C', // Your accent yellow
      light: '#f6d78a',
      dark: '#e6b94d',
      contrastText: '#1F4B48',
    },
    text: {
      primary: '#1F4B48',
      secondary: '#64748b',
      disabled: '#94a3b8',
    },
    background: {
      default: '#EDDFC0',
      paper: '#ffffff',
    },
  };

  // Merge with any provided colors
  const themeColors = { ...defaultColors, ...colors };

  return createTheme({
    palette: {
      primary: {
        main: themeColors.primary.main,
        light: themeColors.primary.light,
        dark: themeColors.primary.dark,
        contrastText: themeColors.primary.contrastText,
      },
      secondary: {
        main: themeColors.secondary.main,
        light: themeColors.secondary.light,
        dark: themeColors.secondary.dark,
        contrastText: themeColors.secondary.contrastText,
      },
      text: {
        primary: themeColors.text.primary,
        secondary: themeColors.text.secondary,
        disabled: themeColors.text.disabled,
      },
      background: {
        default: themeColors.background.default,
        paper: themeColors.background.paper,
      },
    },
    components: {
      MuiButton: {
        styleOverrides: {
          root: {
            textTransform: 'none',
            fontWeight: 500,
          },
          containedPrimary: {
            backgroundColor: themeColors.primary.main,
            '&:hover': {
              backgroundColor: themeColors.primary.dark,
            },
          },
          containedSecondary: {
            backgroundColor: themeColors.secondary.main,
            color: themeColors.secondary.contrastText,
            '&:hover': {
              backgroundColor: themeColors.secondary.dark,
            },
          },
        },
      },
      MuiTabs: {
        styleOverrides: {
          indicator: {
            backgroundColor: themeColors.primary.main,
          },
        },
      },
      MuiTab: {
        styleOverrides: {
          root: {
            '&.Mui-selected': {
              color: themeColors.primary.main,
            },
          },
        },
      },
    },
  });
};

// Default theme instance
export const defaultTheme = createAppTheme();
