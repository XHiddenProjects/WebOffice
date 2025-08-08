#!/usr/bin/env python
import psutil
import sys
import json
class Hardware:
    
    def __init__(self):
        pass
    def __secs2hours(self,secs:int|float)->str:
        """Converts seconds to hours

        Args:
            secs (int | float): Seconds

        Returns:
            str: Formatted timestamp
        """
        mm, ss = divmod(secs, 60)
        hh, mm = divmod(mm, 60)
        return "%d:%02d:%02d" % (hh, mm, ss)
    def cpu(self, config:dict[float,bool,bool])->dict[str,any]:
        """Returns the devices CPU information

        Args:
            config (dict[float,bool,bool]): Configurations using "interval", "percpu", and "logical"

        Returns:
            dict[str,any]: Returns the object of the CPU information
        """
        return {
            'percent': psutil.cpu_percent(config.get('interval',1),config.get('percpu',False)),
            'cores': psutil.cpu_count(config.get('logical',True)),
            'freq':psutil.cpu_freq(config.get('percpu',False)),
            'status': psutil.cpu_stats(),
            'times': psutil.cpu_times(config.get('percpu',False)),
            'times_percent':psutil.cpu_times_percent(config.get('interval',1),config.get('percpu',False))
        }
    def battery(self)->dict[str,bool,float,str]:
        """Returns devices battery information

        Returns:
            dict[str,bool,float,str]: Battery information
        """
        battery = psutil.sensors_battery()
        return {
            'percent': battery.percent,
            'plugged_in': battery.power_plugged,
            'secsleft': battery.secsleft,
            'secsleft_formatted': self.__secs2hours(battery.secsleft)
        }
    def fans(self)->dict[str,float,float]:
        """Returns the devices fan information

        Returns:
            dict[str,float,float]: Fans information
        """
        fans = psutil.sensors_fans()
        if fans:
            # fans is a list of dictionaries, each representing a fan
            fan_info = {}
            for index, fan in enumerate(fans):
                # fan is a dict with keys like 'name' and 'current'
                name = fan.get('name', f'Fan {index + 1}')
                speed = fan.get('current', 'Unknown')
                fan_info[name] = speed
            return fan_info
        else:
            return {'error':'No fan information'}
    def temperature(self, fahrenheit: bool=True):
        temps = psutil.sensors_temperatures(fahrenheit)
        temperature_dict = {}
        for sensor_name, entries in temps.items():
            # entries is a list of named tuples
            temperature_dict[sensor_name] = []
            for entry in entries:
                temperature_dict[sensor_name].append({
                    'label': entry.label,
                    'current': entry.current,
                    'high': entry.high,
                    'critical': entry.critical
                })
        return temperature_dict
def main():
    hardware = Hardware()
    args = sys.argv[1:]
    if '--cpu' in args:
        # You can customize config as needed
        config = {
            'interval': 1,
            'percpu': False,
            'logical': True
        }
        cpu_info = hardware.cpu(config)
        print(json.dumps(cpu_info))
    elif '--battery' in args:
        battery_info = hardware.battery()
        print(json.dumps(battery_info))
    elif '--fans' in args:
        fans_info = hardware.fans()
        print(json.dumps(fans_info))
    elif '--temperature' in args:
        isF = False if '--celsius' in args else True
        temp = hardware.temperature(isF)
        print(json.dumps(temp))
    else:
        print("Invalid method")
if __name__ == '__main__':
    main()