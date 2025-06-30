import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { 
  FormControl, 
  Select, 
  MenuItem, 
  Box, 
  Typography, 
  CircularProgress,
  Grid,
  InputLabel,
  Tabs,
  Tab,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper
} from '@mui/material';
import DashboardWidget from './DashboardWidget';

const TopPerformersWidget = ({ 
  widgetId, 
  settings = { type: 'services', timeframe: 'month', limit: 5 }, 
  onDelete, 
  onSettingsChange 
}) => {
  const [type, setType] = useState(settings.type || 'services');
  const [period, setPeriod] = useState(settings.timeframe || 'month');
  const [limit, setLimit] = useState(settings.limit || 5);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [data, setData] = useState([]);
  const [locationId, setLocationId] = useState(settings.locationId || null);
  const [locations, setLocations] = useState([]);

  // Fetch locations for the dropdown
  useEffect(() => {
    axios.get('/api/locations')
      .then(response => {
        setLocations(response.data.data || []);
      })
      .catch(error => {
        console.error('Error fetching locations:', error);
      });
  }, []);

  // Fetch top performers data based on type, period, and location
  useEffect(() => {
    setLoading(true);
    setError(null);

    const endpoint = type === 'services' ? 'top-services' : 'top-staff';
    const params = { 
      period, 
      limit 
    };
    
    if (locationId) {
      params.location_id = locationId;
    }

    axios.get(`/api/dashboard/analytics/${endpoint}`, { params })
      .then(response => {
        setData(response.data.data || []);
        setLoading(false);
      })
      .catch(error => {
        console.error(`Error fetching top ${type}:`, error);
        setError(`Failed to load top ${type} data. Please try again.`);
        setLoading(false);
      });
  }, [type, period, limit, locationId]);

  // Handle type change
  const handleTypeChange = (event, newValue) => {
    setType(newValue);
    
    if (onSettingsChange) {
      onSettingsChange({ ...settings, type: newValue });
    }
  };

  // Handle period change
  const handlePeriodChange = (event) => {
    const newPeriod = event.target.value;
    setPeriod(newPeriod);
    
    if (onSettingsChange) {
      onSettingsChange({ ...settings, timeframe: newPeriod });
    }
  };

  // Handle location change
  const handleLocationChange = (event) => {
    const newLocationId = event.target.value;
    setLocationId(newLocationId);
    
    if (onSettingsChange) {
      onSettingsChange({ ...settings, locationId: newLocationId });
    }
  };

  // Format currency
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(value);
  };

  return (
    <DashboardWidget 
      title={`Top ${type === 'services' ? 'Services' : 'Staff'}`}
      onDelete={onDelete}
      onSettings={() => {}}
      hasSettings={true}
      className="top-performers-widget"
    >
      <Tabs 
        value={type} 
        onChange={handleTypeChange} 
        aria-label="top performers tabs"
        variant="fullWidth"
        sx={{ mb: 2 }}
      >
        <Tab value="services" label="Services" />
        <Tab value="staff" label="Staff" />
      </Tabs>

      <Grid container spacing={2} sx={{ mb: 2 }}>
        <Grid item xs={6}>
          <FormControl fullWidth size="small">
            <InputLabel id="period-select-label">Period</InputLabel>
            <Select
              labelId="period-select-label"
              id="period-select"
              value={period}
              label="Period"
              onChange={handlePeriodChange}
            >
              <MenuItem value="week">This Week</MenuItem>
              <MenuItem value="month">This Month</MenuItem>
              <MenuItem value="quarter">This Quarter</MenuItem>
              <MenuItem value="year">This Year</MenuItem>
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={6}>
          <FormControl fullWidth size="small">
            <InputLabel id="location-select-label">Location</InputLabel>
            <Select
              labelId="location-select-label"
              id="location-select"
              value={locationId || ''}
              label="Location"
              onChange={handleLocationChange}
              displayEmpty
            >
              <MenuItem value="">All Locations</MenuItem>
              {locations.map(location => (
                <MenuItem key={location.id} value={location.id}>
                  {location.name}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Grid>
      </Grid>

      <Box sx={{ height: 300 }}>
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
            <CircularProgress />
          </Box>
        ) : error ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
            <Typography color="error">{error}</Typography>
          </Box>
        ) : data.length === 0 ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
            <Typography>No data available for the selected period</Typography>
          </Box>
        ) : (
          <TableContainer component={Paper} sx={{ maxHeight: 300, overflow: 'auto' }}>
            <Table stickyHeader aria-label="top performers table" size="small">
              <TableHead>
                <TableRow>
                  <TableCell>Rank</TableCell>
                  <TableCell>Name</TableCell>
                  <TableCell align="right">Revenue</TableCell>
                  <TableCell align="right">
                    {type === 'services' ? 'Bookings' : 'Appointments'}
                  </TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {data.map((item, index) => (
                  <TableRow key={item.id}>
                    <TableCell>{index + 1}</TableCell>
                    <TableCell>{item.name}</TableCell>
                    <TableCell align="right">{formatCurrency(item.revenue)}</TableCell>
                    <TableCell align="right">
                      {type === 'services' ? item.booking_count : item.appointment_count}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        )}
      </Box>
    </DashboardWidget>
  );
};

export default TopPerformersWidget;
