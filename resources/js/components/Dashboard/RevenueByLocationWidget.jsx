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
  BarChart, 
  Bar, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  Legend, 
  ResponsiveContainer 
} from 'recharts';
import DashboardWidget from './DashboardWidget';

const RevenueByLocationWidget = ({ 
  widgetId, 
  settings = { timeframe: 'month' }, 
  onDelete, 
  onSettingsChange 
}) => {
  const [period, setPeriod] = useState(settings.timeframe || 'month');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [data, setData] = useState([]);

  // Fetch revenue by location data
  useEffect(() => {
    setLoading(true);
    setError(null);

    axios.get('/api/dashboard/analytics/revenue-by-location', { 
      params: { period }
    })
      .then(response => {
        setData(response.data.data || []);
        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching revenue by location:', error);
        setError('Failed to load location revenue data. Please try again.');
        setLoading(false);
      });
  }, [period]);

  // Handle period change
  const handlePeriodChange = (event) => {
    const newPeriod = event.target.value;
    setPeriod(newPeriod);
    
    if (onSettingsChange) {
      onSettingsChange({ ...settings, timeframe: newPeriod });
    }
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
      title="Revenue by Location" 
      onDelete={onDelete}
      onSettings={() => {}}
      hasSettings={true}
      className="revenue-by-location-widget"
    >
      <Grid container spacing={2}>
        <Grid item xs={12}>
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
            <Typography>No location revenue data available for the selected period</Typography>
          </Box>
        ) : (
          <ResponsiveContainer width="100%" height="100%">
            <BarChart
              data={data}
              margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
            >
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="location_name" />
              <YAxis 
                tickFormatter={(value) => `$${value}`}
                domain={['auto', 'auto']}
              />
              <Tooltip content={<CustomTooltip />} />
              <Legend />
              <Bar 
                dataKey="revenue" 
                fill="#8884d8" 
                name="Revenue"
              />
            </BarChart>
          </ResponsiveContainer>
        )}
      </Box>
    </DashboardWidget>
  );
};

export default RevenueByLocationWidget;
