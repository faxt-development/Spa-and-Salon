import React, { createContext, useContext, useMemo } from 'react';
import { ThemeProvider as MuiThemeProvider } from '@mui/material/styles';
import { createAppTheme } from './index';

// Create a context for the theme
const ThemeContext = createContext({});

/**
 * Custom ThemeProvider that integrates Material-UI with your app's theming system
 * @param {Object} props - Component props
 * @param {React.ReactNode} props.children - Child components
 * @param {Object} [props.colors] - Optional custom colors to override the default theme
 * @returns {JSX.Element} Themed application
 */
export const AppThemeProvider = ({ children, colors = {} }) => {
  // Create theme based on provided colors or use defaults
  const theme = useMemo(() => createAppTheme(colors), [colors]);

  // Value to provide to context consumers
  const contextValue = useMemo(
    () => ({
      colors: {
        primary: theme.palette.primary.main,
        secondary: theme.palette.secondary.main,
        accent: theme.palette.accent?.main || '#F4C96C',
      },
      updateColors: (newColors) => {
        // This would typically be implemented to update the theme at runtime
        // You can implement this based on your state management solution
        console.warn('Theme color updates not implemented. Use a state management solution.');
      },
    }),
    [theme]
  );

  return (
    <ThemeContext.Provider value={contextValue}>
      <MuiThemeProvider theme={theme}>{children}</MuiThemeProvider>
    </ThemeContext.Provider>
  );
};

/**
 * Hook to access the theme context
 * @returns {Object} Theme context with colors and update function
 */
export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within an AppThemeProvider');
  }
  return context;
};

export default AppThemeProvider;
