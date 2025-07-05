import * as React from 'react';
import { useState } from 'react';
import { Card, CardHeader, CardContent, CardActions, IconButton, Menu, MenuItem, Typography } from '@mui/material';
import MoreVertIcon from '@mui/icons-material/MoreVert';
import SettingsIcon from '@mui/icons-material/Settings';
import DeleteIcon from '@mui/icons-material/Delete';

const DashboardWidget = ({ 
  title, 
  children, 
  onEdit, 
  onDelete, 
  onSettings, 
  hasSettings = false,
  className = ''
}) => {
  const [anchorEl, setAnchorEl] = useState(null);
  const open = Boolean(anchorEl);

  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleEdit = () => {
    handleClose();
    if (onEdit) onEdit();
  };

  const handleDelete = () => {
    handleClose();
    if (onDelete) onDelete();
  };

  const handleSettings = () => {
    handleClose();
    if (onSettings) onSettings();
  };

  return (
    <Card className={`dashboard-widget ${className}`} elevation={2}>
      <CardHeader
        title={<Typography variant="h6">{title}</Typography>}
        action={
          <IconButton 
            aria-label="widget-menu"
            aria-controls={open ? 'widget-menu' : undefined}
            aria-haspopup="true"
            aria-expanded={open ? 'true' : undefined}
            onClick={handleClick}
          >
            <MoreVertIcon />
          </IconButton>
        }
      />
      <CardContent className="dashboard-widget-content">
        {children}
      </CardContent>
      <Menu
        id="widget-menu"
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        MenuListProps={{
          'aria-labelledby': 'widget-menu-button',
        }}
      >
        {hasSettings && (
          <MenuItem onClick={handleSettings}>
            <SettingsIcon fontSize="small" sx={{ mr: 1 }} />
            Settings
          </MenuItem>
        )}
        <MenuItem onClick={handleDelete}>
          <DeleteIcon fontSize="small" sx={{ mr: 1 }} />
          Remove Widget
        </MenuItem>
      </Menu>
    </Card>
  );
};

export default DashboardWidget;
