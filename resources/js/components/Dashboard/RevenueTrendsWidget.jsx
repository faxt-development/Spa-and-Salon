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
  InputLabel
} from '@mui/material';
import { 
  LineChart, 
  Line, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  Legend, 
  ResponsiveContainer 
} from 'recharts';
import DashboardWidget from './DashboardWidget';

const RevenueTrendsWidget = ({ 
  widgetId, 
  settings = { timeframe: 'month', compare: true }, 
  onDelete, 
  onSettingsChange 
}) => {
  const [period, setPeriod] = useState(settings.timeframe || 'month');
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

  // Fetch revenue data based on period and location
  useEffect(() => {
    setLoading(true);
    setError(null);

    const params = { period };
    if (locationId) {
      params.location_id = locationId;
    }

    axios.get('/api/dashboard/analytics/revenue-trends', { params })
      .then(response => {
        setData(response.data.data || []);
        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching revenue trends:', error);
        setError('Failed to load revenue data. Please try again.');
        setLoading(false);
      });
  }, [period, locationId]);

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

  // Format the data for the chart
  const formatData = (data) => {
    if (!data || data.length === 0) return [];

    return data.map(item => {
      let label;
      
      switch (period) {
        case 'daily':
          label = new Date(item.date).toLocaleDateString();
          break;
        case 'weekly':
          label = `Week ${item.week_number}`;
          break;
        case 'monthly':
          label = item.month_name;
          break;
        default:
          label = item.date || item.month || item.week;
      }

      return {
        name: label,
        revenue: parseFloat(item.revenue || 0).toFixed(2),
      };
    });
  };

  // Format currency
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(value);
  };

  // Custom tooltip for the chart
  const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload && payload.length) {
      return (
        <Box className="custom-tooltip" sx={{ bgcolor: 'background.paper', p: 2, border: '1px solid #ccc', borderRadius: 1 }}>
          <Typography variant="subtitle2">{label}</Typography>
          <Typography variant="body2" color="primary">
            Revenue: {formatCurrency(payload[0].value)}
          </Typography>
        </Box>
      );
    }
    return null;
  };

  return (
    <DashboardWidget 
      title="Revenue Trends" 
      onDelete={onDelete}
      onSettings={() => {}}
      hasSettings={true}
      className="revenue-trends-widget"
    >
      <Grid container spacing={2}>
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
              <MenuItem value="daily">Daily</MenuItem>
              <MenuItem value="weekly">Weekly</MenuItem>
              <MenuItem value="monthly">Monthly</MenuItem>
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

      <Box sx={{ height: 300, mt: 2 }}>
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
            <Typography>No revenue data available for the selected period</Typography>
          </Box>
        ) : (
          <ResponsiveContainer width="100%" height="100%">
            <LineChart
              data={formatData(data)}
              margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis 
                tickFormatter={(value) => `$${value}`}
                domain={['auto', 'auto']}
              />
              <Tooltip content={<CustomTooltip />} />
              <Legend />
              <Line 
                type="monotone" 
                dataKey="revenue" 
                stroke="#8884d8" 
                activeDot={{ r: 8 }} 
                name="Revenue"
              />
            </LineChart>
          </ResponsiveContainer>
        )}
      </Box>
    </DashboardWidget>
  );
};

export default RevenueTrendsWidget;
