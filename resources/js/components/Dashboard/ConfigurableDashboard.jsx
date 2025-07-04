import React, { useState, useEffect } from 'react';
import axios from 'axios';
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

const ConfigurableDashboard = ({ userType = 'admin', compact = false }) => {
  const [widgets, setWidgets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [addWidgetDialogOpen, setAddWidgetDialogOpen] = useState(false);
  const [snackbar, setSnackbar] = useState({ open: false, message: '', severity: 'success' });

  // Fetch user's dashboard widgets
  useEffect(() => {
    setLoading(true);
    setError(null);

    axios.get('/api/dashboard/widgets')
      .then(response => {
        setWidgets(response.data.data || []);
        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching dashboard widgets:', error);
        setError('Failed to load dashboard widgets. Please try again.');
        setLoading(false);
      });
  }, []);

  // Handle widget deletion
  const handleDeleteWidget = (widgetId) => {
    axios.delete(`/api/dashboard/widgets/${widgetId}`)
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
    axios.put(`/api/dashboard/widgets/${widgetId}`, { settings })
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
    let widgetData = {
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

    axios.post('/api/dashboard/widgets', widgetData)
      .then(response => {
        setWidgets([...widgets, response.data.data]);
        setAddWidgetDialogOpen(false);
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

    const reorderedWidgets = Array.from(widgets);
    const [removed] = reorderedWidgets.splice(result.source.index, 1);
    reorderedWidgets.splice(result.destination.index, 0, removed);

    // Update positions
    const updatedWidgets = reorderedWidgets.map((widget, index) => ({
      ...widget,
      position: index
    }));

    setWidgets(updatedWidgets);

    // Update layout on the server
    const layout = updatedWidgets.map(widget => ({
      id: widget.id,
      position: widget.position
    }));

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
                          md={compact ? 4 : 6} 
                          lg={compact ? 3 : 4}
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
