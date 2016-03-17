/**
 * Вычисляет скорость производства ресурсов (ед/час) и энергии.
 * @param techID ID постройки - рудника/синтезатора, электростанции или солн.спутника.
 * @param techLevel уровень постройки или кол-во спутников
 * @param energyTechLevel уровень энерг. технологии
 * @param plasmaTechLevel уровень плазменной технологии
 * @param maxTemp макс. температура на планете
 * @param universeSpeedFactor множитель скорости вселенной
 * @param geologist флаг наличия Геолога
 * @param engineer флаг наличия Инженера
 * @param productionFactor коэффициент производства (0..1, меньше 1, если энергии не хватает)
 * @param powerFactor процент мощности (0..1, устанавливается пользователем)
 * @param boosterType тип ускорителя: 0-отсутствует, 1-бронза (10%), 2-серебро (20%), 3-золото (30%) 
 * @returns Кол-во производимых ресурсов или энергии
 */
function getProductionRate(techID, techLevel, energyTechLevel, plasmaTechLevel, maxTemp, universeSpeedFactor, geologist, engineer, productionFactor, powerFactor, boosterType) {
	if (techLevel < 1)
		return 0;
	var geologistFactor = (geologist === true) ? 1.25 : 1.0;
	var engineerFactor = (engineer === true) ? 1.15 : 1.0;
	var boostFactor = 1.0 + boosterType*0.1;
	var productionRate;	
	switch (techID*1) {
		case 1:
			productionRate = Math.round(boostFactor * productionFactor * Math.floor(universeSpeedFactor * 30.0 * techLevel * Math.pow(1.1, techLevel) * geologistFactor  * powerFactor));
			productionRate += Math.round(productionRate * 0.01 * plasmaTechLevel); 
			break;
		case 2:
			productionRate = Math.round(boostFactor * productionFactor * Math.floor(universeSpeedFactor * 20.0 * techLevel * Math.pow(1.1, techLevel) * geologistFactor * powerFactor));
			productionRate += Math.round(productionRate * 0.0066 * plasmaTechLevel);
			break;
		case 3:
			productionRate = Math.round(boostFactor * productionFactor * Math.floor(universeSpeedFactor * 10.0 * techLevel * Math.pow(1.1, techLevel) * (1.44 - 0.004 * maxTemp) * geologistFactor * powerFactor));
			break;
		case 4:
			productionRate = Math.floor(20.0 * techLevel * Math.pow(1.1, techLevel) * engineerFactor * powerFactor * (1 + energyTechLevel * 0.01));
			break;
		case 12:
			productionRate = Math.floor(30.0 * techLevel * Math.pow((1.05 + energyTechLevel * 0.01), techLevel) * engineerFactor * powerFactor);
			break;
		case 212:
			productionRate = techLevel * Math.floor((maxTemp + 140) / 6) * engineerFactor * powerFactor;
			// Если в калькуляторе ввести температуру планеты меньше -140, он будет показывать, что солнечные спутники производят отрицательное кол-во энергии. Нехорошо это.
			if (productionRate < 0) {
				productionRate = 0;
			}
			break;
		default: {
			productionRate = 0;
		}
	}
	return productionRate;
}

/**
 * Вычисляет потребление энергиии рудниками и дейтерия термоядерной электростанцией
 * @param techID ID рудника или электростанции
 * @param techLevel уровень постройки
 * @param universeSpeedFactor множитель скорости вселенной 
 * @param powerFactor процент мощности (0..1, устанавливается пользователем)
 * @returns Кол-во потребляемых постройкой единиц энергии/дейтерия
 */
function getHourlyConsumption(techID, techLevel, universeSpeedFactor, powerFactor) {
	if (techLevel < 1)
		return 0;
	var consump;
	switch (techID*1) {
		case 1: // рудник металла. потребляет энергию
		case 2: // рудник кристалла. потребляет энергию
			consump = Math.ceil(10.0 * techLevel * Math.pow(1.1, techLevel) * powerFactor);
			break;
		case 12: // термоядерная электростанция. потребляет дейтерий
			consump = Math.ceil(10.0 * techLevel * Math.pow(1.1, techLevel) * powerFactor) * universeSpeedFactor;
			break;
		case 3: // синтезатор дейтерия. потребляет энергию
			consump = Math.ceil(20.0 * techLevel * Math.pow(1.1, techLevel) * powerFactor);
			break;
		default:
			return 0;
	}
	return consump;
}
