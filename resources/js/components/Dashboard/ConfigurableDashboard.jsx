import React, { useState, useEffect } from 'react';
// Import regular axios for type definitions, but we'll use the global instance
import axios from 'axios';

// Use the global axios instance configured in bootstrap.js
// bootstrap.js sets this up as window.axios
const api = window.axios;
import {
  Box,
  Typography,
  CircularProgress,
  Button,
  Grid,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  Divider,
  IconButton,
  Snackbar,
  Alert
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import DashboardIcon from '@mui/icons-material/Dashboard';
import TrendingUpIcon from '@mui/icons-material/TrendingUp';
import PeopleIcon from '@mui/icons-material/People';
import PlaceIcon from '@mui/icons-material/Place';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

import RevenueTrendsWidget from './RevenueTrendsWidget';
import TopPerformersWidget from './TopPerformersWidget';
import RevenueByLocationWidget from './RevenueByLocationWidget';

const ConfigurableDashboard = ({ userType = 'admin', compact = false, columns = 3 }) => {
  const [widgets, setWidgets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [addWidgetDialogOpen, setAddWidgetDialogOpen] = useState(false);
  const [snackbar, setSnackbar] = useState({ open: false, message: '', severity: 'success' });

  // Fetch user's dashboard widgets
  useEffect(() => {
    const fetchWidgets = async () => {
      setLoading(true);
      setError(null);

      try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Set the CSRF token in the headers
        api.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        api.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(
            document.cookie.replace(/(?:(?:^|.*;\s*)XSRF-TOKEN\s*\=\s*([^;]*).*$)|^.*$/, '$1')
        );

        const response = await api.get('/dashboard/widgets');
        setWidgets(response.data.data || []);
      } catch (error) {
        console.error('Error fetching dashboard widgets:', error);

        if (error.response) {
          // Handle specific error statuses
          if (error.response.status === 401) {
            setError('Session not recognized by the api call.');

          } else {
            setError(error.response.data.message || 'Failed to load dashboard widgets. Please try again.');
          }
        } else {
          setError('Network error. Please check your connection and try again.');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchWidgets();
  }, []);

  // Handle widget deletion
  const handleDeleteWidget = (widgetId) => {
    api.delete(`/dashboard/widgets/${widgetId}`)
      .then(() => {
        setWidgets(widgets.filter(widget => widget.id !== widgetId));
        setSnackbar({
          open: true,
          message: 'Widget removed successfully',
          severity: 'success'
        });
      })
      .catch(error => {
        console.error('Error deleting widget:', error);
        setSnackbar({
          open: true,
          message: 'Failed to remove widget',
          severity: 'error'
        });
      });
  };

  // Handle widget settings update
  const handleWidgetSettingsUpdate = (widgetId, settings) => {
    api.put(`/dashboard/widgets/${widgetId}`, { settings })
      .then(response => {
        setWidgets(widgets.map(widget =>
          widget.id === widgetId ? { ...widget, settings: response.data.data.settings } : widget
        ));
      })
      .catch(error => {
        console.error('Error updating widget settings:', error);
        setSnackbar({
          open: true,
          message: 'Failed to update widget settings',
          severity: 'error'
        });
      });
  };

  // Handle adding a new widget
  const handleAddWidget = (type) => {
    setAddWidgetDialogOpen(false);
    
    const widgetData = {
      type,
      settings: {}
    };

    switch (type) {
      case 'revenue_overview':
        widgetData.settings = { timeframe: 'month', compare: true };
        break;
      case 'top_services':
      case 'top_staff':
        widgetData.settings = { timeframe: 'month', limit: 5 };
        break;
      case 'revenue_by_location':
        widgetData.settings = { timeframe: 'month' };
        break;
    }

    api.post('/dashboard/widgets', widgetData)
      .then(response => {
        setWidgets([...widgets, response.data.data]);
        setSnackbar({
          open: true,
          message: 'Widget added successfully',
          severity: 'success'
        });
      })
      .catch(error => {
        console.error('Error adding widget:', error);
        setSnackbar({
          open: true,
          message: 'Failed to add widget',
          severity: 'error'
        });
      });
  };

  // Handle drag and drop to reorder widgets
  const handleDragEnd = (result) => {
    if (!result.destination) return;

    const items = Array.from(widgets);
    const [reorderedItem] = items.splice(result.source.index, 1);
    items.splice(result.destination.index, 0, reorderedItem);

    setWidgets(items);

    // Update order in the backend
    const widgetOrder = items.map((widget, index) => ({
      id: widget.id,
      order: index + 1
    }));

    api.post('/dashboard/widgets/reorder', { widgets: widgetOrder })
    axios.put('/api/dashboard/preferences', { layout })
      .catch(error => {
        console.error('Error updating dashboard layout:', error);
        setSnackbar({
          open: true,
          message: 'Failed to save dashboard layout',
          severity: 'error'
        });
      });
  };

  // Render widget based on type
  const renderWidget = (widget) => {
    switch (widget.type) {
      case 'revenue_overview':
        return (
          <RevenueTrendsWidget
            widgetId={widget.id}
            settings={widget.settings}
            onDelete={() => handleDeleteWidget(widget.id)}
            onSettingsChange={(settings) => handleWidgetSettingsUpdate(widget.id, settings)}
          />
        );
      case 'top_services':
        return (
          <TopPerformersWidget
            widgetId={widget.id}
            settings={{ ...widget.settings, type: 'services' }}
            onDelete={() => handleDeleteWidget(widget.id)}
            onSettingsChange={(settings) => handleWidgetSettingsUpdate(widget.id, settings)}
          />
        );
      case 'top_staff':
        return (
          <TopPerformersWidget
            widgetId={widget.id}
            settings={{ ...widget.settings, type: 'staff' }}
            onDelete={() => handleDeleteWidget(widget.id)}
            onSettingsChange={(settings) => handleWidgetSettingsUpdate(widget.id, settings)}
          />
        );
      case 'revenue_by_location':
        return (
          <RevenueByLocationWidget
            widgetId={widget.id}
            settings={widget.settings}
            onDelete={() => handleDeleteWidget(widget.id)}
            onSettingsChange={(settings) => handleWidgetSettingsUpdate(widget.id, settings)}
          />
        );
      default:
        return (
          <Box sx={{ p: 3, textAlign: 'center' }}>
            <Typography>Unknown widget type: {widget.type}</Typography>
          </Box>
        );
    }
  };

  // Add Widget Dialog
  const AddWidgetDialog = () => (
    <Dialog
      open={addWidgetDialogOpen}
      onClose={() => setAddWidgetDialogOpen(false)}
      maxWidth="sm"
      fullWidth
    >
      <DialogTitle>Add Dashboard Widget</DialogTitle>
      <DialogContent>
        <List>
          <ListItem button onClick={() => handleAddWidget('revenue_overview')}>
            <ListItemIcon>
              <TrendingUpIcon />
            </ListItemIcon>
            <ListItemText
              primary="Revenue Overview"
              secondary="Shows revenue trends over time (daily, weekly, monthly)"
            />
          </ListItem>
          <Divider />
          <ListItem button onClick={() => handleAddWidget('top_services')}>
            <ListItemIcon>
              <DashboardIcon />
            </ListItemIcon>
            <ListItemText
              primary="Top Services"
              secondary="Shows top performing services by revenue"
            />
          </ListItem>
          <Divider />
          <ListItem button onClick={() => handleAddWidget('top_staff')}>
            <ListItemIcon>
              <PeopleIcon />
            </ListItemIcon>
            <ListItemText
              primary="Top Staff"
              secondary="Shows top performing staff by revenue"
            />
          </ListItem>
          <Divider />
          <ListItem button onClick={() => handleAddWidget('revenue_by_location')}>
            <ListItemIcon>
              <PlaceIcon />
            </ListItemIcon>
            <ListItemText
              primary="Revenue by Location"
              secondary="Shows revenue breakdown by location"
            />
          </ListItem>
        </List>
      </DialogContent>
      <DialogActions>
        <Button onClick={() => setAddWidgetDialogOpen(false)}>Cancel</Button>
      </DialogActions>
    </Dialog>
  );

  return (
    <Box sx={{ p: compact ? 0 : 3 }}>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h5" component="h1">
          {compact ? 'Analytics Widgets' : 'Dashboard'}
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => setAddWidgetDialogOpen(true)}
          size={compact ? 'small' : 'medium'}
          sx={{
            backgroundColor: 'var(--accent-500, #F4C96C)',
            '&:hover': {
              backgroundColor: 'var(--accent-600, #E8B53D)',
            },
          }}
        >
          Add Widget
        </Button>
      </Box>

      {loading ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', p: 5 }}>
          <CircularProgress />
        </Box>
      ) : error ? (
        <Box sx={{ p: 3, textAlign: 'center' }}>
          <Typography color="error">{error}</Typography>
          <Button
            variant="outlined"
            sx={{ mt: 2 }}
            onClick={() => window.location.reload()}
          >
            Retry
          </Button>
        </Box>
      ) : widgets.length === 0 ? (
        <Box sx={{ p: 5, textAlign: 'center', border: '2px dashed #ccc', borderRadius: 2 }}>
          <Typography variant="h6" sx={{ mb: 2 }}>No widgets added yet</Typography>
          <Typography variant="body1" sx={{ mb: 3 }}>
            Add widgets to customize your dashboard
          </Typography>
 <Button
            variant="contained"
            startIcon={<AddIcon />}
            onClick={() => setAddWidgetDialogOpen(true)}
            sx={{
              backgroundColor: 'var(--accent-500, #F4C96C)',
              '&:hover': {
                backgroundColor: 'var(--accent-600, #E8B53D)',
              },
            }}
          >
            Add Your First Widget
          </Button>
        </Box>
      ) : (
        <DragDropContext onDragEnd={handleDragEnd}>
          <Droppable droppableId="dashboard">
            {(provided) => (
              <Grid
                container
                spacing={3}
                ref={provided.innerRef}
                {...provided.droppableProps}
              >
                {widgets
                  .sort((a, b) => a.position - b.position)
                  .map((widget, index) => (
                    <Draggable key={widget.id} draggableId={`widget-${widget.id}`} index={index}>
                      {(provided) => (
                        <Grid
                          item
                          xs={12}
                          md={columns === 4 ? 3 : (compact ? 4 : 6)}
                          lg={columns === 4 ? 3 : (compact ? 3 : 4)}
                          ref={provided.innerRef}
                          {...provided.draggableProps}
                          {...provided.dragHandleProps}
                        >
                          {renderWidget(widget)}
                        </Grid>
                      )}
                    </Draggable>
                  ))}
                {provided.placeholder}
              </Grid>
            )}
          </Droppable>
        </DragDropContext>
      )}

      <AddWidgetDialog />

      <Snackbar
        open={snackbar.open}
        autoHideDuration={6000}
        onClose={() => setSnackbar({ ...snackbar, open: false })}
      >
        <Alert
          onClose={() => setSnackbar({ ...snackbar, open: false })}
          severity={snackbar.severity}
          sx={{ width: '100%' }}
        >
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
};

export default ConfigurableDashboard;
