#!/.venv/bin/python
import psutil
import sys
import json
import GPUtil
import cpuinfo
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
            'percent': psutil.cpu_percent(config.get('per_interval',None),config.get('per_percpu',True)),
            'cores': psutil.cpu_count(config.get('cores_logical',True)),
            'freq':psutil.cpu_freq(config.get('freq_percpu',True)),
            'status': psutil.cpu_stats(),
            'times': psutil.cpu_times(config.get('times_percpu',True)),
            'times_percent':psutil.cpu_times_percent(config.get('times_per_interval',None),config.get('times_per_percpu',True))
        }
    def Processor(self):
        """Returns the processor information

        Returns:
            dict: A dictionary containing CPU info
        """
        cpu_info = cpuinfo.get_cpu_info()
        return {
            'brand': cpu_info.get('brand_raw', 'Unknown'),
            'hz_advertised': cpu_info.get('hz_advertised', 'Unknown'),
            'hz_actual': cpu_info.get('hz_actual', 'Unknown'),
            'arch': cpu_info.get('arch', 'Unknown'),
            'bits': cpu_info.get('bits', 'Unknown'),
            'count': cpu_info.get('count', 'Unknown'),
            'vendor_id': cpu_info.get('vendor_id_raw', 'Unknown'),
            'l1_data_cache_size': cpu_info.get('l1_data_cache_size', 'Unknown'),
            'l1_instruction_cache_size': cpu_info.get('l1_instruction_cache_size', 'Unknown'),
            'l2_cache_size': cpu_info.get('l2_cache_size', 'Unknown'),
            'l3_cache_size': cpu_info.get('l3_cache_size', 'Unknown'),
            'flags': cpu_info.get('flags', [])
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
    def GPU(self):
        """Returns GPU information using GPUtil"""
        gpus = GPUtil.getGPUs()
        gpu_list = []
        if not gpus:
            return {'error': 'No GPU found'}
        for gpu in gpus:
            gpu_info = {
                'id': gpu.id,
                'name': gpu.name,
                'load': gpu.load * 100,  # convert to percentage
                'free_memory': gpu.memoryFree,
                'used_memory': gpu.memoryUsed,
                'total_memory': gpu.memoryTotal,
                'temperature': gpu.temperature,
                'driver': gpu.driver,
            }
            gpu_list.append(gpu_info)
        return gpu_list
    def Memory(self):
        """Returns the memory information

        Returns:
            dict: Memory information with keys 'memory' and 'swap_memory'
                Each contains a dict with details like total, available, used, free, etc.
        """
        mem = psutil.virtual_memory()
        swap = psutil.swap_memory()

        return {
            'memory': {
                'total': mem.total,
                'available': mem.available,
                'used': mem.used,
                'free': mem.free,
                'active': getattr(mem, 'active', None),
                'inactive': getattr(mem, 'inactive', None),
                'buffers': getattr(mem, 'buffers', None),
                'cached': getattr(mem, 'cached', None),
                'shared': getattr(mem, 'shared', None),
                'slab': getattr(mem, 'slab', None),
            },
            'swap_memory': {
                'total': swap.total,
                'used': swap.used,
                'free': swap.free,
                'sin': swap.sin,
                'sout': swap.sout,
            }
        }
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
    elif '--gpu' in args:
        gpu = hardware.GPU()
        print(json.dumps(gpu))
    elif '--memory' in args:
        memory_info = hardware.Memory()
        print(json.dumps(memory_info))
    elif '--processor' in args:
        processor_info = hardware.Processor()
        print(json.dumps(processor_info))
    else:
        print("Invalid method")
if __name__ == '__main__':
    main()