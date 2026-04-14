<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SupplierRecommendationService
{
    /**
     * Known-active manufacturers (from the Excel formula).
     */
    private const ACTIVE_MANUFACTURERS = [
        'texas', 'issi', 'omron', 'micron', 'on semi', 'onsemi',
        'semikron', 'stm', 'st microelectronic', 'analog devices',
    ];

    /**
     * Component category → type map (from the Component Type sheet).
     * Stored as lowercase keys for case-insensitive lookup.
     */
    private static array $categoryMap = [
        '16-bit microcontrollers - mcu' => 'Active',
        '32-bit microcontrollers - mcu' => 'Active',
        '8-bit microcontrollers - mcu' => 'Active',
        'ac power plugs & receptacles' => 'Passive',
        'accelerometers' => 'Active',
        'adafruit accessories' => 'Active',
        'aluminium organic polymer capacitors' => 'Passive',
        'analog comparators' => 'Active',
        'analogue panel meters' => 'Passive',
        'analogue switch ics' => 'Active',
        'antennas' => 'Passive',
        'antistatic control products' => 'Passive',
        'arm microcontrollers - mcu' => 'Active',
        'attenuators - interconnects' => 'Passive',
        'audio amplifiers' => 'Active',
        'automotive connectors' => 'Passive',
        'battery contacts' => 'Passive',
        'bipolar transistors - bjt' => 'Active',
        'board mount temperature sensors' => 'Active',
        'board to board & mezzanine connectors' => 'Passive',
        'cable mounting & accessories' => 'Passive',
        'cable ties' => 'Passive',
        'can interface ic' => 'Active',
        'circuit board hardware - pcb' => 'Passive',
        'circular mil spec backshells' => 'Passive',
        'circular mil spec connector' => 'Passive',
        'circular mil spec strain reliefs & adapters' => 'Passive',
        'circular mil spec tools, hardware & accessories' => 'Passive',
        'circular push pull connectors' => 'Passive',
        'coaxial cables' => 'Passive',
        'common mode chokes / filters' => 'Passive',
        'counter shift registers' => 'Active',
        'crystals' => 'Passive',
        'current sense amplifiers' => 'Active',
        'current sense resistors - smd' => 'Passive',
        'd-sub standard connectors' => 'Passive',
        'desktop ac adapters' => 'Active',
        'digital isolators' => 'Active',
        'eeprom' => 'Active',
        'emi feedthrough filters' => 'Passive',
        'esd protection diodes / tvs diodes' => 'Active',
        'feed through capacitors' => 'Passive',
        'ferrite beads' => 'Passive',
        'ffc & fpc connectors' => 'Passive',
        'gan fets' => 'Passive',
        'gate drivers' => 'Active',
        'headers & wire housings' => 'Passive',
        'heat shrink cable boots & end caps' => 'Passive',
        'heat shrink tubing & sleeves' => 'Passive',
        'high speed operational amplifiers' => 'Active',
        'isolated dc/dc converters - smd' => 'Passive',
        'isolated dc/dc converters - through hole' => 'Active',
        'ldo voltage regulators' => 'Active',
        'led lighting driver ics' => 'Active',
        'lin transceivers' => 'Active',
        'logic gates' => 'Passive',
        'memory card connectors' => 'Passive',
        'mems microphones' => 'Active',
        'modular connectors / ethernet connectors' => 'Passive',
        'mosfets' => 'Active',
        'motor/motion/ignition controllers & drivers' => 'Active',
        'multi-conductor cables' => 'Passive',
        'multilayer ceramic capacitors mlcc - smd/smt' => 'Passive',
        'multiprotocol modules' => 'Active',
        'nan' => 'Passive',
        'nor flash' => 'Active',
        'operational amplifiers - op amps' => 'Active',
        'piezo buzzers & audio indicators' => 'Passive',
        'power inductors - smd' => 'Passive',
        'power line filters' => 'Passive',
        'power management specialised - pmic' => 'Active',
        'ptc (positive temperature coefficient) thermistors' => 'Passive',
        'punches & dies' => 'Passive',
        'real time clock' => 'Active',
        'rectifiers' => 'Active',
        'resettable fuses - pptc' => 'Passive',
        'resonators' => 'Passive',
        'rf adapters - between series' => 'Passive',
        'rf amplifier' => 'Passive',
        'rf cable assemblies' => 'Passive',
        'rf connectors / coaxial connectors' => 'Passive',
        'rf front end' => 'Passive',
        'rf inductors - smd' => 'Passive',
        'rf system on a chip - soc' => 'Passive',
        'rf terminators' => 'Passive',
        'rf transceiver' => 'Passive',
        'rheostats' => 'Passive',
        'rs-232 interface ic' => 'Active',
        'rs-422/rs-485 interface ic' => 'Active',
        'rs-485 interface ic' => 'Active',
        'schottky diodes & rectifiers' => 'Active',
        'sensor fixings & accessories' => 'Passive',
        'signal conditioning' => 'Passive',
        'single colour leds' => 'Active',
        'slide switches' => 'Active',
        'small signal switching diodes' => 'Active',
        'solder sleeves & shield tubing' => 'Passive',
        'soldering irons' => 'Passive',
        'sram' => 'Passive',
        'standard circular connector' => 'Passive',
        'standard clock oscillators' => 'Passive',
        'standoffs & spacers' => 'Passive',
        'supervisory circuits' => 'Active',
        'surface mount fuses' => 'Passive',
        'switching voltage regulators' => 'Active',
        'tactile switches' => 'Passive',
        'tantalum capacitors - polymer' => 'Passive',
        'tantalum capacitors - solid smd' => 'Passive',
        'thick film resistors - smd' => 'Passive',
        'thin film resistors - smd' => 'Passive',
        'toggle switches' => 'Active',
        'translation - voltage levels' => 'Passive',
        'usb cables / ieee 1394 cables' => 'Passive',
        'usb connectors' => 'Passive',
        'usb interface ic' => 'Active',
        'voltage references' => 'Active',
        'white leds' => 'Active',
        'wire labels & markers' => 'Passive',
        'zener diodes' => 'Active',
        'wirewound resistors - chassis mount' => 'Passive',
        'varistors' => 'Passive',
        'transistor output optocouplers' => 'Active',
        'thermal imaging cameras' => 'Active',
        'terminals' => 'Passive',
        'speciality ceramic capacitors' => 'Passive',
        'solid state relays - pcb mount' => 'Active',
        'sic mosfets' => 'Active',
        'security ics / authentication ics' => 'Active',
        'screws & fasteners' => 'Passive',
        'screwdrivers, nut drivers & socket drivers' => 'Passive',
        'safety capacitors' => 'Passive',
        'racks & rack cabinet accessories' => 'Passive',
        'programmer accessories' => 'Passive',
        'power switch ics - power distribution' => 'Active',
        'other tools' => 'Passive',
        'non-isolated dc/dc converters' => 'Active',
        'n/a' => 'Passive',
        'metal film resistors - through hole' => 'Passive',
        'low signal relays - pcb' => 'Active',
        'industrial temperature sensors' => 'Active',
        'high speed optocouplers' => 'Active',
        'general purpose relays' => 'Active',
        'fixed terminal blocks' => 'Passive',
        'film capacitors' => 'Passive',
        'emergency stop switches / e-stop switches' => 'Active',
        'dip switches/sip switches' => 'Active',
        'digital potentiometer ics' => 'Active',
        'd-sub tools & hardware' => 'Passive',
        'current transformers' => 'Active',
        'current & power monitors & regulators' => 'Active',
        'crimpers / crimping tools' => 'Passive',
        'buffers & line drivers' => 'Active',
        'audio transformers / signal transformers' => 'Passive',
        'ac/dc power modules' => 'Active',
        'aluminium electrolytic capacitors - radial leaded' => 'Passive',
        'communication ics - various' => 'Active',
        'emi filter circuits' => 'Passive',
        'bridge rectifiers' => 'Active',
        'multilayer ceramic capacitors mlcc - leaded' => 'Passive',
        'scrs (silicon controlled rectifiers)' => 'Active',
        'scrs' => 'Active',
        'analog front end - afe' => 'Active',
        'analog to digital converters - adc' => 'Active',
        'development boards & kits - other processors' => 'Active',
        'distance sensor ics & embedded modules' => 'Active',
        'circuit breakers' => 'Passive',
        'high speed/modular connectors' => 'Passive',
        'hook-up wire' => 'Passive',
        'industrial pressure sensors' => 'Passive',
        'analog to digital converters (adc)' => 'Active',
        'analog front end (afe)' => 'Active',
        'wirewound resistors - through hole' => 'Passive',
        'triacs' => 'Active',
        'linear voltage regulators' => 'Active',
        'gas discharge tubes - gdts / gas plasma arrestors' => 'Passive',
        'precision amplifiers' => 'Active',
        'voltage to frequency & frequency to voltage' => 'Active',
        'power inductors - leaded' => 'Passive',
        'rf inductors - leaded' => 'Passive',
        'ac/dc converters' => 'Active',
        'gnss / gps modules' => 'Active',
        'multiprotocol development tools' => 'Active',
        'rf adapters - in series' => 'Passive',
        'coin cell battery holders' => 'Battery',
        'coin cell battery' => 'Battery',
        'ffc / fpc jumper cables' => 'Passive',
        'supercapacitors / ultracapacitors' => 'Passive',
        'aluminium electrolytic capacitors - axial leaded' => 'Active',
        'aluminium electrolytic capacitors - smd' => 'Active',
        'aluminium electrolytic capacitors - snap in' => 'Active',
        'din 41612 connectors' => 'Passive',
        'power to the board' => 'Passive',
        'standard card edge connectors' => 'Passive',
        'heavy duty power connectors' => 'Passive',
        'fuse holder accessories' => 'Passive',
        'automotive fuses' => 'Passive',
        'cartridge fuses' => 'Passive',
        'tft displays & accessories' => 'Active',
        'timers' => 'Active',
        'serializers & deserializers - serdes' => 'Active',
        'interface - codecs' => 'Active',
        'isolation amplifiers' => 'Active',
        'board mount surge protectors' => 'Passive',
        'latches' => 'Active',
        'switching controllers' => 'Active',
        'ethernet ics' => 'Active',
        'bus transceivers' => 'Active',
        'panel mount indicator lamps' => 'Active',
        'led panel mount indicators' => 'Active',
        'multi-colour leds' => 'Active',
        'oled displays & accessories' => 'Active',
        'logic output opto-couplers' => 'Active',
        'contactors - electromechanical' => 'Active',
        'test plugs & test jacks' => 'Passive',
        'industrial relays' => 'Active',
        'current sense resistors - through hole' => 'Passive',
        'ic & component sockets' => 'Active',
        'switching power supplies' => 'Active',
        'heat sinks' => 'Passive',
        'ntc (negative temperature coefficient) thermistors' => 'Active',
        'test probes' => 'Passive',
        'battery management' => 'Active',
        'board mount hall effect/magnetic sensors' => 'Active',
        'nand flash' => 'Active',
        'ethernet cables / networking cables' => 'Passive',
        'conduit fittings & accessories' => 'Passive',
        'fpga - field programmable gate array' => 'Active',
        'clock generators & support products' => 'Active',
        'pci express/pci connectors' => 'Passive',
        'differential amplifiers' => 'Active',
        'digital to analog converters - dac' => 'Active',
        'video ics' => 'Active',
        'i/o connectors' => 'Passive',
        'delay lines/timing elements' => 'Active',
        'board mount current sensors' => 'Active',
        'board mount pressure sensors' => 'Active',
        'instrumentation amplifiers' => 'Active',
        'rotary switches' => 'Passive',
        'thick film resistors - through hole' => 'Passive',
        'inductor kits & accessories' => 'Passive',
        'capacitor kits' => 'Passive',
        'rf connector accessories' => 'Passive',
        'pluggable terminal blocks' => 'Passive',
        'd-sub micro-d connectors' => 'Passive',
        'circular metric connectors' => 'Passive',
        'power transformers' => 'Passive',
        'hot swap voltage controllers' => 'Active',
        'interface - i/o expanders' => 'Active',
        'rectangular cable assemblies' => 'Passive',
    ];

    /**
     * Determine if a manufacturer name is "Active" based on the known list.
     */
    public static function isActiveManufacturer(string $manufacturer): bool
    {
        $lower = strtolower($manufacturer);
        foreach (self::ACTIVE_MANUFACTURERS as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine component type (Active / Passive / null) from a category string.
     */
    public static function getTypeFromCategory(?string $category): ?string
    {
        if (!$category) return null;
        return self::$categoryMap[strtolower(trim($category))] ?? null;
    }

    /**
     * Determine component type for an item row.
     * Priority: manufacturer name keywords → category lookup.
     *
     * @param  string|null  $manufacturer  From sourcing_results (mouser → digikey → ti)
     * @param  string|null  $category      From sourcing_results
     * @return string|null  'Active' | 'Passive' | null
     */
    public static function resolveType(?string $manufacturer, ?string $category): ?string
    {
        if ($manufacturer && self::isActiveManufacturer($manufacturer)) {
            return 'Active';
        }
        return self::getTypeFromCategory($category);
    }

    /**
     * Get top-5 recommended SMO suppliers for a given component type.
     * Returns array of supplier names (up to 5, unique, non-empty).
     *
     * @param  string|null  $type  'Active' | 'Passive'
     * @return string[]
     */
    public static function getTopSuppliers(?string $type): array
    {
        if (!$type || !in_array($type, ['Active', 'Passive'])) {
            return [];
        }

        $column = $type === 'Active' ? 'active_count' : 'passive_count';

        $suppliers = DB::table('smo_suppliers')
            ->where($column, '>', 0)
            ->orderBy($column, 'desc')
            ->limit(5)
            ->pluck('name')
            ->toArray();

        return $suppliers;
    }
}
