<?php

namespace App\Services;

class SupplierBrandService
{
    /**
     * Supplier-Brands data: supplier => [brand => count, ...]
     * Parsed from the Supplier-Brands Excel sheet.
     */
    private static array $data = [
        'Ariat' => ['Amphenol'=>10,'Bourns'=>5,'Coilcraft'=>1,'Diodes Incorporated'=>2,'Infineon'=>1,'Kemet'=>1,'Kingbright'=>1,'Microchip'=>1,'Molex'=>1,'Murata'=>6,'NXP Semiconductors'=>3,'Omron'=>1,'Panasonic'=>28,'Rohm Semiconductor'=>2,'STMicroelectronics'=>16,'Taiyo Yuden'=>14,'TDK'=>12,'TE Connectivity'=>7,'Texas instruments'=>15,'Vishay'=>16,'Yageo'=>2],
        'Asai Kosan' => ['AMD / Xilinx'=>29,'Analog Devices'=>574,'Broadcom'=>1,'Infineon'=>1,'Microchip'=>8,'Murata'=>4,'NXP Semiconductors'=>1,'Renesas'=>1,'Rohm Semiconductor'=>1,'STMicroelectronics'=>93,'Taiyo Yuden'=>1,'TDK'=>13,'Texas instruments'=>1,'Vishay'=>2],
        'ATP' => ['Allegro MicroSystems'=>1,'Alps Alpine'=>3,'Amphenol'=>1,'Analog Devices'=>7,'Bourns'=>5,'Bussmann / Eaton'=>2,'C&K'=>2,'Coilcraft'=>3,'CUI Inc.'=>2,'Diodes Incorporated'=>9,'Epcos'=>2,'Infineon'=>3,'ISSI'=>1,'Kemet'=>8,'Kingbright'=>3,'Littelfuse'=>1,'Maxim'=>4,'Microchip'=>2,'Molex'=>1,'Murata'=>334,'NXP Semiconductors'=>11,'Omron'=>1,'ON Semiconductor'=>4,'Onsemi'=>1,'Panasonic'=>59,'Renesas'=>1,'Rohm Semiconductor'=>51,'Samsung'=>472,'Schurter'=>1,'Semtech'=>7,'STMicroelectronics'=>1,'Taiyo Yuden'=>293,'TDK'=>14,'TE Connectivity'=>1,'Texas instruments'=>202,'Vishay'=>272,'Wurth Elektronik'=>30,'Yageo'=>893],
        'Brightmile' => ['Analog Devices'=>2,'Infineon'=>1,'Kemet'=>1,'Microchip'=>1,'Murata'=>1,'NXP Semiconductors'=>1,'Panasonic'=>1,'Rohm Semiconductor'=>1,'STMicroelectronics'=>1,'Taiyo Yuden'=>6,'Texas instruments'=>2,'Vishay'=>1],
        'CapXon' => [],
        'Flyking' => ['Kemet'=>2],
        'Holtek' => [],
        'Kehuite' => ['Allegro MicroSystems'=>8,'AMD / Xilinx'=>24,'Analog Devices'=>307,'Bourns'=>5,'Broadcom'=>2,'C&K'=>1,'Coilcraft'=>44,'Diodes Incorporated'=>5,'Epcos'=>1,'Infineon'=>31,'ISSI'=>7,'Kemet'=>1,'Kingbright'=>4,'Lattice'=>2,'Maxim'=>1,'Microchip'=>60,'Molex'=>1,'Murata'=>110,'NXP Semiconductors'=>15,'Omron'=>1,'ON Semiconductor'=>2,'Panasonic'=>38,'Renesas'=>1,'Rohm Semiconductor'=>23,'Samsung'=>206,'Semtech'=>2,'STMicroelectronics'=>38,'Taiyo Yuden'=>321,'TDK'=>13,'TE Connectivity'=>1,'Texas instruments'=>169,'Vishay'=>147,'Wurth Elektronik'=>75,'Yageo'=>452],
        'Liangxin' => ['Vishay'=>3,'Yageo'=>1],
        'Linkic' => [],
        'Matec' => ['Analog Devices'=>13],
        'Maxtronic' => ['Allegro MicroSystems'=>14,'AMD / Xilinx'=>9,'Analog Devices'=>748,'Bourns'=>25,'Broadcom'=>4,'C&K'=>1,'Coilcraft'=>110,'CUI Inc.'=>1,'Diodes Incorporated'=>15,'Epcos'=>1,'Infineon'=>134,'ISSI'=>15,'Kemet'=>2,'Kingbright'=>14,'Lattice'=>1,'Maxim'=>3,'Microchip'=>206,'Molex'=>1,'Murata'=>169,'NXP Semiconductors'=>16,'Omron'=>1,'ON Semiconductor'=>8,'Panasonic'=>206,'Renesas'=>1,'Rohm Semiconductor'=>28,'Samsung'=>806,'Semtech'=>7,'STMicroelectronics'=>45,'Taiyo Yuden'=>141,'TDK'=>7,'TE Connectivity'=>2,'Texas instruments'=>260,'Vishay'=>147,'Wurth Elektronik'=>75,'Yageo'=>452],
        'Perceptive' => ['Allegro MicroSystems'=>15,'AMD / Xilinx'=>2,'Analog Devices'=>248,'Bourns'=>37,'Broadcom'=>2,'C&K'=>1,'Coilcraft'=>77,'CUI Inc.'=>1,'Diodes Incorporated'=>7,'Epcos'=>1,'Infineon'=>132,'ISSI'=>11,'Kemet'=>6,'Kingbright'=>5,'Lattice'=>1,'Maxim'=>3,'Microchip'=>87,'Molex'=>1,'Murata'=>83,'NXP Semiconductors'=>2,'Omron'=>1,'ON Semiconductor'=>5,'Panasonic'=>133,'Renesas'=>1,'Rohm Semiconductor'=>53,'Samsung'=>452,'Semtech'=>4,'STMicroelectronics'=>41,'Taiyo Yuden'=>25,'TDK'=>9,'TE Connectivity'=>2,'Texas instruments'=>134,'Vishay'=>125,'Wurth Elektronik'=>134,'Yageo'=>452],
        'Pinrex' => [],
        'Samwha' => [],
        'Shainor' => ['Analog Devices'=>1,'Bourns'=>1,'Infineon'=>5,'Kemet'=>1,'Murata'=>1,'Panasonic'=>99,'Rohm Semiconductor'=>1,'Taiyo Yuden'=>2,'Vishay'=>50,'Yageo'=>1],
        'SMYG' => ['3M'=>3,'Allegro MicroSystems'=>1,'Alps Alpine'=>1,'AMD / Xilinx'=>5,'Analog Devices'=>26,'Bourns'=>76,'Broadcom'=>1,'C&K'=>1,'Coilcraft'=>14,'CUI Inc.'=>1,'Diodes Incorporated'=>50,'Epcos'=>1,'Infineon'=>6,'ISSI'=>6,'Kemet'=>13,'Kingbright'=>2,'Maxim'=>1,'Microchip'=>121,'Molex'=>1,'Murata'=>60,'NXP Semiconductors'=>19,'Omron'=>1,'ON Semiconductor'=>3,'Panasonic'=>121,'Renesas'=>1,'Rohm Semiconductor'=>4,'Samsung'=>118,'Semtech'=>1,'STMicroelectronics'=>42,'Taiyo Yuden'=>97,'TDK'=>4,'TE Connectivity'=>1,'Texas instruments'=>141,'Vishay'=>92,'Wurth Elektronik'=>19,'Yageo'=>180],
        'USIE' => ['Bourns'=>1,'Coilcraft'=>2,'Infineon'=>3,'Kemet'=>4,'Microchip'=>11,'Murata'=>4,'NXP Semiconductors'=>1,'Panasonic'=>4,'Rohm Semiconductor'=>1,'Samsung'=>4,'STMicroelectronics'=>4,'Texas instruments'=>9,'Vishay'=>7,'Yageo'=>4],
    ];

    /**
     * Given a manufacturer/brand name, return the top N SMO suppliers
     * that carry that brand, ranked by count descending.
     * Skips duplicates (same supplier won't appear twice).
     *
     * @param string $brand  The manufacturer name (e.g. "Texas instruments")
     * @param int    $limit  How many suppliers to return (default 4)
     * @return array         Array of supplier names
     */
    public static function getTopSuppliersForBrand(string $brand, int $limit = 4): array
    {
        if (empty($brand)) {
            return [];
        }

        // Case-insensitive brand match
        $brandLower = strtolower(trim($brand));

        $scores = [];
        foreach (self::$data as $supplier => $brands) {
            foreach ($brands as $b => $count) {
                if (strtolower($b) === $brandLower) {
                    $scores[$supplier] = $count;
                    break;
                }
            }
        }

        if (empty($scores)) {
            return [];
        }

        // Sort by count descending
        arsort($scores);

        return array_slice(array_keys($scores), 0, $limit);
    }
}
